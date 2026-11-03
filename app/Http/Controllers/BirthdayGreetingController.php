<?php

namespace App\Http\Controllers;

use App\Models\BirthdayGreeting;
use App\Models\User;
use App\Services\RobaseSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class BirthdayGreetingController extends Controller
{
    public function store(Request $request, User $student, RobaseSmsService $sms)
    {
        abort_unless($student->isStudent(), 404);
        abort_unless(
            $student->date_of_birth
                && $student->date_of_birth->month === now()->month
                && $student->date_of_birth->day === now()->day,
            404
        );

        $validated = $request->validate([
            'message.' . $student->id => ['required', 'string', 'max:1000'],
            'send_sms.' . $student->id => ['sometimes', 'boolean'],
            'open_whatsapp.' . $student->id => ['sometimes', 'boolean'],
        ]);
        $sendSms = $request->boolean('send_sms.' . $student->id);
        $openWhatsapp = $request->boolean('open_whatsapp.' . $student->id);
        $rawPhone = $student->parent_phone_number ?: $student->whatsapp_number;
        if (! filled($rawPhone)) {
            $rawPhone = $student->parents()
                ->get()
                ->map(fn (User $parent) => $parent->parent_phone_number ?: $parent->whatsapp_number)
                ->filter()
                ->first();
        }
        $recipient = filled($rawPhone) ? $sms->normalizeNigerianPhone((string) $rawPhone) : null;

        if (($sendSms || $openWhatsapp) && ! $recipient) {
            throw ValidationException::withMessages([
                'channels.' . $student->id => 'Add a valid parent or guardian phone number before choosing SMS or WhatsApp.',
            ]);
        }

        $today = now()->toDateString();
        $existingGreeting = BirthdayGreeting::where('student_id', $student->id)
            ->whereDate('greeting_date', $today)
            ->first();

        if ($existingGreeting) {
            return redirect()
                ->route('admin.birthday-greetings.show', $existingGreeting)
                ->with('warning', 'A birthday greeting has already been posted for this learner today.');
        }

        $signature = '— Cambridge International School';
        $message = trim($validated['message'][$student->id]);
        if (! str_ends_with($message, $signature)) {
            $message = rtrim($message) . "\n" . $signature;
        }

        $greeting = BirthdayGreeting::create([
            'student_id' => $student->id,
            'created_by' => Auth::id(),
            'greeting_date' => $today,
            'message' => $message,
            'expires_at' => now()->addDays(7),
            'guardian_phone' => $recipient,
            'whatsapp_requested' => $openWhatsapp,
            'sms_status' => $sendSms ? 'pending' : 'not_requested',
        ]);

        if ($sendSms) {
            $this->sendSms($greeting, $sms);
        }

        return redirect()->route('admin.birthday-greetings.show', $greeting);
    }

    public function retrySms(BirthdayGreeting $birthdayGreeting, RobaseSmsService $sms)
    {
        abort_unless($birthdayGreeting->sms_status === 'failed' && $birthdayGreeting->guardian_phone, 404);

        $this->sendSms($birthdayGreeting, $sms);

        return redirect()->route('admin.birthday-greetings.show', $birthdayGreeting);
    }

    public function show(BirthdayGreeting $birthdayGreeting)
    {
        $birthdayGreeting->load(['student.class', 'creator']);
        $whatsappUrl = null;
        if ($birthdayGreeting->whatsapp_requested && $birthdayGreeting->guardian_phone) {
            $number = preg_replace('/\D+/', '', $birthdayGreeting->guardian_phone);
            $whatsappUrl = 'https://wa.me/' . $number . '?text=' . rawurlencode($birthdayGreeting->message);
        }

        return view('admin.birthday-greetings.show', compact('birthdayGreeting', 'whatsappUrl'));
    }

    private function sendSms(BirthdayGreeting $greeting, RobaseSmsService $sms): void
    {
        try {
            $response = $sms->send((string) $greeting->guardian_phone, $greeting->message);
            if (! $response->successful()) {
                $greeting->update([
                    'sms_status' => 'failed',
                    'sms_error' => 'Robase rejected the SMS request (HTTP ' . $response->status() . ').',
                ]);
                Log::warning('Birthday greeting SMS was rejected by Robase.', [
                    'greeting_id' => $greeting->id,
                    'student_id' => $greeting->student_id,
                    'status' => $response->status(),
                ]);

                return;
            }

            $greeting->update([
                'sms_status' => 'sent',
                'sms_sent_at' => now(),
                'sms_error' => null,
            ]);
        } catch (Throwable $exception) {
            $greeting->update([
                'sms_status' => 'unknown',
                'sms_error' => 'SMS delivery could not be confirmed. Verify with Robase before retrying.',
            ]);
            Log::error('Birthday greeting SMS delivery could not be confirmed.', [
                'greeting_id' => $greeting->id,
                'student_id' => $greeting->student_id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
