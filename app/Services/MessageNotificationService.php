<?php

namespace App\Services;

use App\Mail\DirectMessageNotification;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MessageNotificationService
{
    public function send(Message $message): void
    {
        $recipient = $message->recipient;

        if (!$recipient instanceof User) {
            return;
        }

        $this->sendEmail($recipient, $message);
        $this->sendWhatsapp($recipient, $message);
    }

    private function sendEmail(User $recipient, Message $message): void
    {
        if (!$recipient->email) {
            return;
        }

        try {
            Mail::to($recipient->email)->send(new DirectMessageNotification($message));
        } catch (\Throwable $exception) {
            Log::warning('Failed to send direct message email notification.', [
                'message_id' => $message->id,
                'recipient_id' => $recipient->id,
                'recipient_email' => $recipient->email,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function sendWhatsapp(User $recipient, Message $message): void
    {
        $webhookUrl = config('admissions.whatsapp_message_webhook');
        $number = $recipient->notification_whatsapp_number;

        if (!$webhookUrl || !$number) {
            return;
        }

        try {
            Http::timeout(15)->post($webhookUrl, [
                'recipient_name' => $recipient->name,
                'recipient_role' => $recipient->role,
                'recipient_number' => $number,
                'recipient_email' => $recipient->email,
                'message_id' => $message->id,
                'subject' => $message->subject,
                'body' => $message->body,
                'sender_name' => $message->sender?->name,
                'sender_role' => $message->sender?->role,
            ])->throw();
        } catch (\Throwable $exception) {
            Log::warning('Failed to send WhatsApp message notification.', [
                'message_id' => $message->id,
                'recipient_id' => $recipient->id,
                'recipient_number' => $number,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
