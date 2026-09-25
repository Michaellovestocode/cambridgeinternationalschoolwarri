<?php

namespace App\Mail;

use App\Models\ParentFeedback;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ParentFeedbackNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ParentFeedback $feedback)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New parent ' . ucfirst($this->feedback->category) . ' [' . $this->feedback->reference_code . ']');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.parent-feedback-notification');
    }
}
