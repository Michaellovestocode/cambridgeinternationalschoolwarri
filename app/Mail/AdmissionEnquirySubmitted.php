<?php

namespace App\Mail;

use App\Models\AdmissionEnquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdmissionEnquirySubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AdmissionEnquiry $enquiry)
    {
    }

    public function envelope(): Envelope
    {
        $label = $this->enquiry->inquiry_type === \App\Models\AdmissionEnquiry::TYPE_APPLICATION
            ? 'Admission Application'
            : 'Admission Enquiry';

        return new Envelope(
            subject: 'New '.$label.' from '.$this->enquiry->parent_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admission-enquiry-submitted',
        );
    }
}
