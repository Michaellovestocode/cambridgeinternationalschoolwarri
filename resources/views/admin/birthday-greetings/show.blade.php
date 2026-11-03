@extends('layouts.app')

@section('title', 'Birthday Greeting')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    @if(session('warning'))
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 font-semibold text-amber-800" role="status">
            {{ session('warning') }}
        </div>
    @endif

    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
        <p class="text-sm font-bold uppercase tracking-wide text-amber-800">Birthday greeting posted</p>
        <h1 class="mt-2 text-2xl font-black text-gray-900">{{ $birthdayGreeting->student->name }}</h1>
        <p class="mt-1 text-sm text-gray-600">
            Visible on the learner dashboard until {{ $birthdayGreeting->expires_at->format('M j, Y g:i A') }}.
        </p>
    </div>

    <section class="rounded-2xl bg-white p-6 shadow">
        <h2 class="text-lg font-bold text-gray-900">Message</h2>
        <p class="mt-3 whitespace-pre-line rounded-xl bg-gray-50 p-4 text-gray-700">{{ $birthdayGreeting->message }}</p>
    </section>

    <section class="rounded-2xl bg-white p-6 shadow">
        <h2 class="text-lg font-bold text-gray-900">Delivery status</h2>
        @if($birthdayGreeting->sms_status === 'sent')
            <p class="mt-2 font-semibold text-green-700">
                SMS accepted by Robase{{ $birthdayGreeting->sms_sent_at ? ' at ' . $birthdayGreeting->sms_sent_at->format('g:i A') : '' }}.
            </p>
        @elseif($birthdayGreeting->sms_status === 'failed')
            <p class="mt-2 font-semibold text-red-700">Robase rejected the SMS request.</p>
            <p class="mt-1 text-sm text-gray-600">{{ $birthdayGreeting->sms_error }}</p>
        @elseif($birthdayGreeting->sms_status === 'pending')
            <p class="mt-2 font-semibold text-amber-700">SMS is being processed.</p>
        @elseif($birthdayGreeting->sms_status === 'unknown')
            <p class="mt-2 font-semibold text-amber-700">SMS delivery could not be confirmed.</p>
            <p class="mt-1 text-sm text-gray-600">{{ $birthdayGreeting->sms_error }}</p>
        @else
            <p class="mt-2 text-gray-600">No SMS was requested.</p>
        @endif

        @if($birthdayGreeting->whatsapp_requested)
            @if($whatsappUrl)
                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer"
                   class="mt-5 inline-flex rounded-xl bg-green-600 px-5 py-3 font-bold text-white hover:bg-green-700">
                    Open WhatsApp draft
                </a>
                <p class="mt-2 text-xs text-gray-500">WhatsApp opens with the message ready. The admin must press Send.</p>
            @else
                <p class="mt-3 text-sm text-red-700">No valid guardian phone number is available for WhatsApp.</p>
            @endif
        @endif

        @if($birthdayGreeting->sms_status === 'failed')
            <form method="POST" action="{{ route('admin.birthday-greetings.retry-sms', $birthdayGreeting) }}" class="mt-5">
                @csrf
                <button type="submit" class="rounded-xl bg-amber-600 px-5 py-3 font-bold text-white hover:bg-amber-700">
                    Retry SMS
                </button>
                <p class="mt-2 text-xs text-gray-500">Retry is available because Robase returned a rejection.</p>
            </form>
        @elseif($birthdayGreeting->sms_status === 'unknown')
            <p class="mt-4 text-sm font-semibold text-amber-800">Check with Robase before retrying to avoid sending a duplicate SMS.</p>
        @endif
    </section>

    <a href="{{ route('admin.dashboard') }}" class="inline-flex font-semibold text-blue-700 hover:underline">
        Return to admin dashboard
    </a>
</div>
@endsection
