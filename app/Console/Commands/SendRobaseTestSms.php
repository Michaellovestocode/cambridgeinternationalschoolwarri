<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendRobaseTestSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'robase:send-test {phone : Phone number to send to} {message? : Message body}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test SMS using Robase (requires ROBASE_API_KEY in .env)';

    public function handle()
    {
        $phone = $this->argument('phone');
        $message = $this->argument('message') ?? 'Test message from application via Robase.';

        $apiKey = config('services.robase.api_key') ?: env('ROBASE_API_KEY');
        $base = config('services.robase.base_url') ?: env('ROBASE_BASE_URL', 'https://api.robase.dev');

        if (! $apiKey) {
            $this->error('ROBASE_API_KEY is not configured in your .env or config/services.php');
            return 1;
        }

        $url = rtrim($base, '/') . '/v1/sms/send';

        $this->info("Sending test SMS to {$phone} via Robase...");

        try {
            $resp = Http::withToken($apiKey)->post($url, [
                'phone_number' => $phone,
                'message' => $message,
            ]);

            if ($resp->successful()) {
                $this->info('Robase responded: ' . $resp->body());
                return 0;
            }

            $this->error('Robase request failed: HTTP ' . $resp->status());
            $this->line($resp->body());
            Log::error('Robase test send failed', ['status' => $resp->status(), 'body' => $resp->body()]);
            return 2;
        } catch (\Throwable $e) {
            $this->error('Exception while sending Robase SMS: ' . $e->getMessage());
            Log::error('Robase test exception', ['error' => $e->getMessage()]);
            return 3;
        }
    }
}
