<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RobaseSmsService
{
    public function normalizeNigerianPhone(string $raw): ?string
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
                // Use the strict Nigerian-format fallback below.
            }
        }

        $digits = preg_replace('/\D+/', '', $raw);
        if (preg_match('/^0[789]\d{9}$/', $digits)) {
            return '+234' . substr($digits, 1);
        }
        if (preg_match('/^234[789]\d{9}$/', $digits)) {
            return '+' . $digits;
        }
        if (preg_match('/^[789]\d{9}$/', $digits)) {
            return '+234' . $digits;
        }

        return null;
    }

    public function send(string $recipient, string $message): Response
    {
        $apiKey = config('services.robase.api_key');
        if (! $apiKey) {
            throw new RuntimeException('Robase SMS is not configured.');
        }

        $payload = [
            'phone_number' => $recipient,
            'message' => $message,
        ];
        if (filled(config('services.robase.sender'))) {
            $payload['sender'] = config('services.robase.sender');
        }

        return Http::withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout(15)
            ->post(rtrim((string) config('services.robase.base_url', 'https://api.robase.dev'), '/') . '/v1/sms/send', $payload);
    }
}
