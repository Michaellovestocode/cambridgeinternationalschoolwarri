<?php

namespace App\Mail;

use App\Models\AdmissionFormPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdmissionPaymentSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AdmissionFormPayment $payment)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Admission Form Payment Submitted - ' . $this->payment->parent_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admission-payment-submitted',
        );
    }
}
