@extends('layouts.app')

@section('title', auth()->user()->isAdmin() ? 'Admin Messages' : 'Teacher Messages')

@section('content')
@php
    $parentInboxMessages = collect();
    $teacherInboxMessages = collect();
    $parentSentMessages = collect();
    $teacherSentMessages = collect();

    if (auth()->user()->isAdmin()) {
        $parentInboxMessages = $receivedMessages->filter(fn ($message) => $message->sender?->role === 'parent')->values();
        $teacherInboxMessages = $receivedMessages->filter(fn ($message) => $message->sender?->role === 'teacher')->values();
        $parentSentMessages = $sentMessages->filter(fn ($message) => collect($message->recipient_roles ?? [])->contains('parent'))->values();
        $teacherSentMessages = $sentMessages->filter(fn ($message) => collect($message->recipient_roles ?? [])->contains('teacher'))->values();
    }
@endphp

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900">Messages</h1>
            <p class="text-sm text-gray-500">
                @if(auth()->user()->isAdmin())
                    Parent-admin and teacher-admin communication lives here. Teachers cannot see parent messages.
                @else
                    Send private messages to admin only.
                @endif
            </p>
        </div>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.parents.index') }}" class="text-sm text-blue-600 hover:underline">Go to parents page for parent messaging</a>
        @endif
    </div>

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl px-6 py-4 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white rounded-3xl shadow-lg p-6 space-y-4">
            @if(auth()->user()->isAdmin())
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Send Message To Teachers</h2>
                    <p class="text-sm text-gray-500">Tick one teacher for a direct message, or select several teachers for a general staff message.</p>
                </div>

                <form method="POST" action="{{ route('admin.messages.store') }}" class="space-y-4" id="teacher-message-form">
                    @csrf
                    <div class="rounded-2xl bg-blue-50 px-4 py-3 text-sm text-blue-700 flex items-center justify-between">
                        <span>Selected teachers</span>
                        <strong id="selected-teachers-count">0</strong>
                    </div>
                    <div class="border border-gray-200 rounded-2xl p-4 max-h-72 overflow-y-auto space-y-3">
                        <label class="flex items-center gap-3 text-sm font-semibold text-gray-700">
                            <input type="checkbox" id="select-all-teachers" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500" />
                            Select all teachers
                        </label>
                        @forelse($teachers as $teacher)
                            <label class="flex items-start gap-3 border border-gray-100 rounded-xl px-3 py-3">
                                <input type="checkbox" class="teacher-message-checkbox rounded border-gray-300 text-purple-600 focus:ring-purple-500 mt-1" value="{{ $teacher->id }}" />
                                <span>
                                    <span class="block font-semibold text-gray-900">{{ $teacher->name }}</span>
                                    <span class="block text-xs text-gray-500">{{ $teacher->email ?: $teacher->registration_number }}</span>
                                </span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-500">No teachers found.</p>
                        @endforelse
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Subject</label>
                        <input name="subject" value="{{ old('subject') }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Optional subject" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Message</label>
                        <textarea name="body" rows="6" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Type the message for the selected teachers...">{{ old('body') }}</textarea>
                    </div>
                    <div id="teacher-message-hidden-inputs"></div>
                    <button type="submit" id="teacher-message-submit" class="bg-gray-900 hover:bg-black text-white font-semibold px-5 py-3 rounded-xl shadow disabled:opacity-50" disabled>
                        Send To Teachers
                    </button>
                </form>
            @else
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Send Message To Admin</h2>
                    <p class="text-sm text-gray-500">This channel is private between teachers and admin.</p>
                </div>

                <form method="POST" action="{{ route('admin.messages.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Subject</label>
                        <input name="subject" value="{{ old('subject') }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Optional subject" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Message</label>
                        <textarea name="body" rows="7" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Type your message to admin...">{{ old('body') }}</textarea>
                    </div>
                    <button type="submit" class="bg-gray-900 hover:bg-black text-white font-semibold px-5 py-3 rounded-xl shadow">
                        Send To Admin
                    </button>
                </form>
            @endif
        </div>

        <div class="bg-white rounded-3xl shadow-lg p-6 space-y-4">
            @if(auth()->user()->isAdmin())
                <div
                    x-data="{
                        inboxTab: localStorage.getItem('adminMessagesInboxTab') || 'parents'
                    }"
                    x-init="$watch('inboxTab', value => localStorage.setItem('adminMessagesInboxTab', value))"
                    class="space-y-4"
                >
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-900">Inbox</h2>
                        <span class="text-xs font-semibold px-3 py-1 rounded-full bg-blue-50 text-blue-700">{{ $receivedMessages->count() }} messages</span>
                    </div>

                    <div class="inline-flex rounded-2xl bg-gray-100 p-1">
                        <button type="button" @click="inboxTab = 'parents'" :class="inboxTab === 'parents' ? 'bg-white text-gray-900 shadow' : 'text-gray-500'" class="px-4 py-2 rounded-xl text-sm font-semibold transition">
                            Parent Messages ({{ $parentInboxMessages->count() }})
                        </button>
                        <button type="button" @click="inboxTab = 'teachers'" :class="inboxTab === 'teachers' ? 'bg-white text-gray-900 shadow' : 'text-gray-500'" class="px-4 py-2 rounded-xl text-sm font-semibold transition">
                            Teacher Messages ({{ $teacherInboxMessages->count() }})
                        </button>
                    </div>

                    <div x-show="inboxTab === 'parents'" class="space-y-3">
                        @forelse($parentInboxMessages as $message)
                            <div class="border border-gray-100 rounded-2xl p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $message->subject ?: 'No subject' }}</p>
                                        <p class="text-xs text-gray-500">From {{ $message->sender?->name ?? 'Unknown sender' }} • Parent</p>
                                    </div>
                                    <span class="text-xs text-gray-400">{{ $message->created_at->format('M j, Y g:i A') }}</span>
                                </div>
                                <p class="text-sm text-gray-700 mt-3 whitespace-pre-line">{{ $message->body }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No parent messages yet.</p>
                        @endforelse
                    </div>

                    <div x-show="inboxTab === 'teachers'" class="space-y-3">
                        @forelse($teacherInboxMessages as $message)
                            <div class="border border-gray-100 rounded-2xl p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $message->subject ?: 'No subject' }}</p>
                                        <p class="text-xs text-gray-500">From {{ $message->sender?->name ?? 'Unknown sender' }} • Teacher</p>
                                    </div>
                                    <span class="text-xs text-gray-400">{{ $message->created_at->format('M j, Y g:i A') }}</span>
                                </div>
                                <p class="text-sm text-gray-700 mt-3 whitespace-pre-line">{{ $message->body }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No teacher messages yet.</p>
                        @endforelse
                    </div>
                </div>
            @else
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
                                    <p class="text-xs text-gray-500">From {{ $message->sender?->name ?? 'Unknown sender' }} • {{ ucfirst($message->sender?->role ?? 'user') }}</p>
                                </div>
                                <span class="text-xs text-gray-400">{{ $message->created_at->format('M j, Y g:i A') }}</span>
                            </div>
                            <p class="text-sm text-gray-700 mt-3 whitespace-pre-line">{{ $message->body }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No admin messages yet.</p>
                    @endforelse
                </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-lg p-6 space-y-4">
        @if(auth()->user()->isAdmin())
            <div
                x-data="{
                    sentTab: localStorage.getItem('adminMessagesSentTab') || 'parents'
                }"
                x-init="$watch('sentTab', value => localStorage.setItem('adminMessagesSentTab', value))"
                class="space-y-4"
            >
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900">Sent Messages</h2>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-emerald-50 text-emerald-700">{{ $sentMessages->count() }} batches</span>
                </div>

                <div class="inline-flex rounded-2xl bg-gray-100 p-1">
                    <button type="button" @click="sentTab = 'parents'" :class="sentTab === 'parents' ? 'bg-white text-gray-900 shadow' : 'text-gray-500'" class="px-4 py-2 rounded-xl text-sm font-semibold transition">
                        To Parents ({{ $parentSentMessages->count() }})
                    </button>
                    <button type="button" @click="sentTab = 'teachers'" :class="sentTab === 'teachers' ? 'bg-white text-gray-900 shadow' : 'text-gray-500'" class="px-4 py-2 rounded-xl text-sm font-semibold transition">
                        To Teachers ({{ $teacherSentMessages->count() }})
                    </button>
                </div>

                <div x-show="sentTab === 'parents'" class="space-y-3">
                    @forelse($parentSentMessages as $message)
                        <div class="border border-gray-100 rounded-2xl p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $message->subject ?: 'No subject' }}</p>
                                    <p class="text-xs text-gray-500">
                                        Sent to {{ $message->recipient_count === 1 ? $message->recipients->first() : $message->recipient_count . ' parents' }}
                                    </p>
                                </div>
                                <span class="text-xs text-gray-400">{{ $message->created_at->format('M j, Y g:i A') }}</span>
                            </div>
                            @if($message->recipient_count > 1)
                                <p class="text-xs text-gray-500 mt-2">{{ $message->recipients->join(', ') }}</p>
                            @endif
                            <p class="text-sm text-gray-700 mt-3 whitespace-pre-line">{{ $message->body }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No messages sent to parents yet.</p>
                    @endforelse
                </div>

                <div x-show="sentTab === 'teachers'" class="space-y-3">
                    @forelse($teacherSentMessages as $message)
                        <div class="border border-gray-100 rounded-2xl p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $message->subject ?: 'No subject' }}</p>
                                    <p class="text-xs text-gray-500">
                                        Sent to {{ $message->recipient_count === 1 ? $message->recipients->first() : $message->recipient_count . ' teachers' }}
                                    </p>
                                </div>
                                <span class="text-xs text-gray-400">{{ $message->created_at->format('M j, Y g:i A') }}</span>
                            </div>
                            @if($message->recipient_count > 1)
                                <p class="text-xs text-gray-500 mt-2">{{ $message->recipients->join(', ') }}</p>
                            @endif
                            <p class="text-sm text-gray-700 mt-3 whitespace-pre-line">{{ $message->body }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No messages sent to teachers yet.</p>
                    @endforelse
                </div>
            </div>
        @else
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900">Sent Messages</h2>
                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-emerald-50 text-emerald-700">{{ $sentMessages->count() }} batches</span>
            </div>

            <div class="space-y-3">
                @forelse($sentMessages as $message)
                    <div class="border border-gray-100 rounded-2xl p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $message->subject ?: 'No subject' }}</p>
                                <p class="text-xs text-gray-500">
                                    Sent to {{ $message->recipient_count === 1 ? $message->recipients->first() : $message->recipient_count . ' recipients' }}
                                </p>
                            </div>
                            <span class="text-xs text-gray-400">{{ $message->created_at->format('M j, Y g:i A') }}</span>
                        </div>
                        @if($message->recipient_count > 1)
                            <p class="text-xs text-gray-500 mt-2">{{ $message->recipients->join(', ') }}</p>
                        @endif
                        <p class="text-sm text-gray-700 mt-3 whitespace-pre-line">{{ $message->body }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No sent messages yet.</p>
                @endforelse
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const selectAllTeachers = document.getElementById('select-all-teachers');
    const teacherCheckboxes = Array.from(document.querySelectorAll('.teacher-message-checkbox'));
    const hiddenInputsContainer = document.getElementById('teacher-message-hidden-inputs');
    const selectedTeachersCount = document.getElementById('selected-teachers-count');
    const teacherSubmitButton = document.getElementById('teacher-message-submit');

    if (!hiddenInputsContainer || !selectedTeachersCount || !teacherSubmitButton) {
        return;
    }

    const syncTeachers = () => {
        const selected = teacherCheckboxes.filter((checkbox) => checkbox.checked);
        hiddenInputsContainer.innerHTML = '';

        selected.forEach((checkbox) => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'recipient_ids[]';
            hiddenInput.value = checkbox.value;
            hiddenInputsContainer.appendChild(hiddenInput);
        });

        selectedTeachersCount.textContent = String(selected.length);
        teacherSubmitButton.disabled = selected.length === 0;

        if (selectAllTeachers) {
            selectAllTeachers.checked = selected.length > 0 && selected.length === teacherCheckboxes.length;
            selectAllTeachers.indeterminate = selected.length > 0 && selected.length < teacherCheckboxes.length;
        }
    };

    if (selectAllTeachers) {
        selectAllTeachers.addEventListener('change', () => {
            teacherCheckboxes.forEach((checkbox) => {
                checkbox.checked = selectAllTeachers.checked;
            });
            syncTeachers();
        });
    }

    teacherCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', syncTeachers);
    });

    syncTeachers();
});
</script>
@endpush
