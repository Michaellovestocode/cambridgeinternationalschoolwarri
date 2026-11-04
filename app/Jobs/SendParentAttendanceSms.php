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
use App\Services\RobaseSmsService;

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
        $body = "Cambridge: {$student->name} {$action} at {$time} on {$date}.";

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
        $sms = app(RobaseSmsService::class);
        foreach ($rawNumbers as $raw) {
            $to = $sms->normalizeNigerianPhone((string) $raw);
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

        foreach ($recipients as $to) {
            $response = $sms->send($to, $body);
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
