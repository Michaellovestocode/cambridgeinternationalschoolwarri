<p>Hello {{ $message->recipient?->name ?? 'there' }},</p>

<p>You have received a new message from {{ $message->sender?->name ?? 'the school' }}.</p>

@if($message->subject)
    <p><strong>Subject:</strong> {{ $message->subject }}</p>
@endif

<p><strong>Message:</strong></p>
<p>{!! nl2br(e($message->body)) !!}</p>

<p>Please log in to the school portal to reply or view the full message history.</p>
