<?php

namespace App\Mail;

use App\Models\AdmissionEnquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdmissionEnquiryAcknowledgement extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AdmissionEnquiry $enquiry)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We received your enquiry',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admission-enquiry-acknowledgement',
        );
    }
}
