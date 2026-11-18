@extends('layouts.app')
@section('title', 'Assignments')
@section('content')
<main class="mx-auto max-w-6xl space-y-5 px-1">
    <header class="rounded-2xl bg-gradient-to-r from-cyan-700 to-blue-700 p-5 text-white shadow-lg sm:p-7">
        <a href="{{ route('student.dashboard') }}" class="text-sm font-bold text-white/80 hover:text-white">← Learner home</a>
        <h1 class="mt-3 text-2xl font-black sm:text-3xl">Assignments</h1>
        <p class="mt-1 text-sm text-white/80">Upcoming deadlines appear first. New assignments are highlighted.</p>
    </header>
    <section class="space-y-3">
        @forelse($assignments as $assignment)
            @php($attempt = $assignment->student_attempt)
            <article class="rounded-2xl border {{ $assignment->is_new_assignment ? 'border-cyan-200 bg-cyan-50/40' : 'border-gray-100 bg-white' }} p-4 shadow-sm sm:p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2"><h2 class="break-words text-lg font-black text-gray-900">{{ $assignment->title }}</h2>@if($assignment->is_new_assignment)<span class="rounded-full bg-cyan-700 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-white">New</span>@endif</div>
                        <p class="mt-1 text-sm font-semibold text-gray-600">{{ $assignment->subject?->name ?? 'Assignment' }} · {{ $assignment->topic }}</p>
                        <p class="mt-2 text-sm {{ $assignment->due_at && $assignment->due_at->isPast() ? 'font-bold text-rose-700' : 'text-gray-600' }}">{{ $assignment->due_at ? 'Due ' . $assignment->due_at->format('D, j M · g:i A') . ($assignment->due_at->isPast() ? ' · Past due' : '') : 'No due date set' }}</p>
                    </div>
                    @if($attempt && ! $attempt->is_published && ! $attempt->allow_resubmission)
                        <span class="shrink-0 rounded-xl bg-amber-100 px-4 py-3 text-center text-sm font-bold text-amber-800">Submitted · awaiting review</span>
                    @elseif($attempt?->is_published && ! $attempt->allow_resubmission)
                        <a href="{{ route('student.learning.result', $attempt) }}" class="shrink-0 rounded-xl bg-gray-100 px-4 py-3 text-center text-sm font-bold text-gray-700">View marked work</a>
                    @else
                        <a href="{{ route('student.learning.show', $assignment) }}" class="shrink-0 rounded-xl bg-cyan-700 px-5 py-3 text-center text-sm font-bold text-white hover:bg-cyan-800">{{ $attempt?->allow_resubmission ? 'Continue assignment' : 'Start assignment' }}</a>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl bg-white p-10 text-center shadow-sm"><p class="font-bold text-gray-800">No assignments yet</p><p class="mt-1 text-sm text-gray-500">Published assignments will appear here.</p></div>
        @endforelse
    </section>
</main>
@endsection
