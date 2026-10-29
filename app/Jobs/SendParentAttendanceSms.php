<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\AttendanceRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;

class SendParentAttendanceSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $studentId;
    public int $recordId;
    public string $eventType; // 'checkin' | 'checkout'

    public int $tries = 5;
    public array $backoff = [30, 120, 300, 600];

    public function __construct(int $studentId, int $recordId, string $eventType = 'checkin')
    {
        $this->studentId = $studentId;
        $this->recordId = $recordId;
        $this->eventType = $eventType;
    }

    public function handle()
    {
        $student = User::find($this->studentId);
        $record = AttendanceRecord::find($this->recordId);
        if (! $student || ! $record) {
            Log::warning('SendParentAttendanceSms: missing student or record', ['student_id'=>$this->studentId,'record_id'=>$this->recordId]);
            return;
        }

        $time = $this->eventType === 'checkin'
            ? ($record->check_in_at?->format('g:i A') ?? now()->format('g:i A'))
            : ($record->check_out_at?->format('g:i A') ?? now()->format('g:i A'));
        $date = $record->attendance_date?->format('M j, Y') ?? now()->format('M j, Y');
        $action = $this->eventType === 'checkin' ? 'checked in' : 'checked out';
        $body = "{$student->name} {$action} at {$time} on {$date}.";

        // Learner guardian number first; linked parent accounts are fallback.
        $rawNumbers = [];
        if ($student->role === 'student' && filled($student->parent_phone_number)) {
            $rawNumbers[] = $student->parent_phone_number;
        } else {
            foreach ($student->parents()->get() as $parent) {
                $raw = $parent->parent_phone_number ?: $parent->whatsapp_number;
                if (filled($raw)) {
                    $rawNumbers[] = $raw;
                }
            }
        }
        if ($rawNumbers === []) {
            Log::warning('Attendance SMS skipped: no guardian phone is configured.', ['student_id' => $student->id, 'record_id' => $record->id]);
            return;
        }

        $recipients = [];
        foreach ($rawNumbers as $raw) {
            $to = $this->formatToE164((string) $raw);
            if (! $to) {
                Log::warning('Attendance SMS skipped: guardian phone is invalid.', ['student_id' => $student->id, 'record_id' => $record->id]);
                continue;
            }
            $recipients[$to] = $to;
        }
        if ($recipients === []) {
            return;
        }

        $apiKey = config('services.robase.api_key');
        if (! $apiKey) {
            Log::error('Attendance SMS cannot be sent: Robase API key is not configured.', ['student_id' => $student->id, 'record_id' => $record->id]);
            return;
        }

        $url = rtrim((string) config('services.robase.base_url', 'https://api.robase.dev'), '/') . '/v1/sms/send';
        foreach ($recipients as $to) {
            $response = Http::withToken($apiKey)->acceptJson()->asJson()->timeout(15)->post($url, [
                'phone_number' => $to,
                'message' => $body,
            ]);
            if (! $response->successful()) {
                Log::warning('Robase attendance SMS failed; queue will retry.', [
                    'student_id' => $student->id,
                    'record_id' => $record->id,
                    'status' => $response->status(),
                ]);
                $response->throw();
            }
            Log::info('Robase attendance SMS accepted.', [
                'student_id' => $student->id,
                'record_id' => $record->id,
                'event_type' => $this->eventType,
                'message_id' => $response->json('id'),
            ]);
        }
    }
    private function formatToE164(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }
        if (class_exists('\\libphonenumber\\PhoneNumberUtil')) {
            try {
                $util = \libphonenumber\PhoneNumberUtil::getInstance();
                $parsed = $util->parse($raw, 'NG');
                if ($util->isValidNumber($parsed)) {
                    return $util->format($parsed, \libphonenumber\PhoneNumberFormat::E164);
                }
            } catch (\Throwable) {
                // Apply strict Nigerian-format fallback below.
            }
        }
        $digits = preg_replace('/\\D+/', '', $raw);
        if (preg_match('/^0[789]\\d{9}$/', $digits)) {
            return '+234' . substr($digits, 1);
        }
        if (preg_match('/^234[789]\\d{9}$/', $digits)) {
            return '+' . $digits;
        }
        if (preg_match('/^[789]\\d{9}$/', $digits)) {
            return '+234' . $digits;
        }
        return null;
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Attendance SMS failed after queue retries.', [
            'student_id' => $this->studentId,
            'record_id' => $this->recordId,
            'event_type' => $this->eventType,
            'error' => $exception->getMessage(),
        ]);
    }
}
