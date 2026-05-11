@extends('layouts.app')

@section('title', 'Parent Messages')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900">Messages</h1>
            <p class="text-sm text-gray-500">Send a direct message to admin and read private replies from admin only.</p>
        </div>
        <a href="{{ route('parent.dashboard') }}" class="text-sm text-blue-600 hover:underline">Back to parent portal</a>
    </div>

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl px-6 py-4 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white rounded-3xl shadow-lg p-6 space-y-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Send Message</h2>
                <p class="text-sm text-gray-500">Your message will go to admin only. Teachers cannot see parent-admin messages.</p>
            </div>

            @if($children->isNotEmpty())
                <div class="rounded-2xl bg-blue-50 px-4 py-3 text-sm text-blue-700">
                    Linked children: {{ $children->map(fn ($child) => $child->name . ' (' . ($child->class?->display_name ?? 'No class') . ')')->join(', ') }}
                </div>
            @endif

            <form method="POST" action="{{ route('parent.messages.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Subject</label>
                    <input name="subject" value="{{ old('subject') }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500" placeholder="Optional short subject" />
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Message</label>
                    <textarea name="body" rows="7" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500" placeholder="Type your message to the school admin team...">{{ old('body') }}</textarea>
                </div>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 py-3 rounded-xl shadow">
                    Send Message
                </button>
            </form>
        </div>

        <div class="bg-white rounded-3xl shadow-lg p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900">Inbox</h2>
                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-blue-50 text-blue-700">{{ $receivedMessages->count() }} messages</span>
            </div>

            <div class="space-y-3">
                @forelse($receivedMessages as $message)
                    <div class="border border-gray-100 rounded-2xl p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $message->subject ?: 'No subject' }}</p>
                                <p class="text-xs text-gray-500">From {{ $message->sender?->name ?? 'School Admin' }} • {{ ucfirst($message->sender?->role ?? 'staff') }}</p>
                            </div>
                            <span class="text-xs text-gray-400">{{ $message->created_at->format('M j, Y g:i A') }}</span>
                        </div>
                        <p class="text-sm text-gray-700 mt-3 whitespace-pre-line">{{ $message->body }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No messages from the school yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-lg p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Sent Messages</h2>
            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-emerald-50 text-emerald-700">{{ $sentMessages->count() }} sent</span>
        </div>

        <div class="space-y-3">
            @forelse($sentMessages as $message)
                <div class="border border-gray-100 rounded-2xl p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $message->subject ?: 'No subject' }}</p>
                            <p class="text-xs text-gray-500">
                                Delivered to {{ $message->recipient_count === 1 ? $message->recipients->first() : $message->recipient_count . ' staff members' }}
                            </p>
                        </div>
                        <span class="text-xs text-gray-400">{{ $message->created_at->format('M j, Y g:i A') }}</span>
                    </div>
                    <p class="text-sm text-gray-700 mt-3 whitespace-pre-line">{{ $message->body }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-500">You have not sent any message yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
