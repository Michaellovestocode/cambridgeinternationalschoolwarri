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

        // Collect parent numbers
        $parents = $student->parents()->get();
        foreach ($parents as $parent) {
            $raw = $parent->parent_phone_number ?: $parent->whatsapp_number ?: null;
            if (! $raw) continue;

            $to = $this->formatToE164($raw);
            if (! $to) {
                Log::warning('Skipping parent SMS: unable to normalize phone', ['raw' => $raw, 'parent_id' => $parent->id]);
                continue;
            }

            // If Twilio env configured, attempt send; otherwise log the message (local testing)
            $sid = env('TWILIO_SID');
            $token = env('TWILIO_TOKEN');
            $from = env('TWILIO_FROM');
            if ($sid && $token && $from) {
                try {
                    // Lazy-load Twilio client to avoid hard dependency in environments without it
                    if (! class_exists('\Twilio\Rest\Client')) {
                        Log::warning('Twilio client not installed; cannot send SMS', ['to'=>$to]);
                        continue;
                    }
                    $client = new \Twilio\Rest\Client($sid, $token);
                    $client->messages->create($to, ['from' => $from, 'body' => $body]);
                } catch (\Throwable $e) {
                    Log::error('Failed to send parent SMS', ['to'=>$to, 'error'=>$e->getMessage()]);
                }
            } else {
                // Try Robase if configured
                $robaseKey = env('ROBASE_API_KEY');
                if ($robaseKey) {
                    try {
                        $payload = [
                            'phone_number' => $to,
                            'message' => $body,
                        ];
                        if ($sender = env('ROBASE_SENDER')) {
                            $payload['sender'] = $sender;
                        }

                        $resp = Http::withToken($robaseKey)
                            ->post(rtrim(env('ROBASE_BASE_URL', 'https://api.robase.dev'), '/') . '/v1/sms/send', $payload);
                        if (! $resp->successful()) {
                            Log::error('Robase SMS send failed', ['to' => $to, 'status' => $resp->status(), 'body' => $resp->body()]);
                        }
                    } catch (\Throwable $e) {
                        Log::error('Robase SMS exception', ['to' => $to, 'error' => $e->getMessage()]);
                    }
                } else {
                    // Local/dev: log the message so developer can verify delivery text
                    Log::info('Parent SMS (dev) queued', ['to' => $to, 'body' => $body]);
                }
            }
        }
    }

    private function formatToE164(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        // Prefer libphonenumber if installed
        if (class_exists('\\libphonenumber\\PhoneNumberUtil')) {
            try {
                $util = \libphonenumber\PhoneNumberUtil::getInstance();
                $defaultRegion = env('DEFAULT_PHONE_REGION', 'NG');
                $numProto = $util->parse($raw, $defaultRegion);
                if ($util->isValidNumber($numProto)) {
                    return $util->format($numProto, \libphonenumber\PhoneNumberFormat::E164);
                }
            } catch (\Throwable $e) {
                Log::warning('libphonenumber parse failed', ['raw' => $raw, 'error' => $e->getMessage()]);
            }
        }

        // Heuristic fallback: remove non-digits (keep +) and attempt basic normalization
        $clean = preg_replace('/[^\d+]/', '', $raw);

        if (str_starts_with($clean, '+')) {
            $digits = preg_replace('/\D/', '', $clean);
            return strlen($digits) >= 8 ? ('+' . $digits) : null;
        }

        $digits = preg_replace('/\D/', '', $raw);
        if ($digits === '') {
            return null;
        }

        // If number starts with 0, assume national format and replace leading zeros
        if (preg_match('/^0+/', $digits)) {
            $digits = preg_replace('/^0+/', '', $digits);
            return env('DEFAULT_COUNTRY_CODE', '+234') . $digits;
        }

        // If it already starts with the default country code (without plus), add plus
        $defaultDigits = ltrim(env('DEFAULT_COUNTRY_CODE', '+234'), '+');
        if (str_starts_with($digits, $defaultDigits)) {
            return '+' . $digits;
        }

        // If it's a local-length number, prepend default country code
        if (strlen($digits) <= 10) {
            return env('DEFAULT_COUNTRY_CODE', '+234') . $digits;
        }

        return null;
    }
}
