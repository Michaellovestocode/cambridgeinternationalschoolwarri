@extends('layouts.app')

@section('title', 'Learner Home')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    @if(($birthdayGreetings ?? collect())->isNotEmpty())
        @foreach($birthdayGreetings as $greeting)
            <section class="rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 to-pink-50 p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-widest text-amber-700">A message from Cambridge</p>
                <p class="mt-2 whitespace-pre-line text-lg font-semibold text-gray-800">{{ $greeting->message }}</p>
                <p class="mt-3 text-xs text-gray-500">Shown until {{ $greeting->expires_at->format('M j') }}.</p>
            </section>
        @endforeach
    @endif

    <header class="rounded-3xl bg-gradient-to-r from-green-600 via-blue-600 to-purple-600 p-6 text-white shadow-xl sm:p-8">
        <p class="text-sm font-bold uppercase tracking-wide text-white/75">Learner home</p>
        <h1 class="mt-2 break-words text-2xl font-black sm:text-4xl">Welcome back, {{ auth()->user()->name }}!</h1>
        <p class="mt-3 text-white/85">{{ auth()->user()->class->display_name ?? auth()->user()->class->name ?? 'Your class' }} · {{ auth()->user()->registration_number }}</p>
    </header>

    <section class="overflow-hidden rounded-2xl bg-white shadow-lg">
        <div class="flex flex-col gap-3 border-b border-gray-100 bg-gradient-to-r from-cyan-50 to-blue-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-cyan-700">Start here</p>
                <h2 class="mt-1 text-xl font-black text-gray-900 sm:text-2xl">Your assignments</h2>
                <p class="mt-1 text-sm text-gray-600">Coming deadlines are listed first. New work is marked for you.</p>
            </div>
            <a href="{{ route('student.assignments.index') }}" class="inline-flex justify-center rounded-xl bg-cyan-700 px-4 py-3 text-sm font-bold text-white hover:bg-cyan-800">View all assignments</a>
        </div>

        <div class="space-y-3 p-4 sm:p-6">
            @forelse($assignments as $assignment)
                @php($attempt = $assignment->student_attempt)
                <article class="rounded-2xl border {{ $assignment->is_new_assignment ? 'border-cyan-200 bg-cyan-50/40' : 'border-gray-100 bg-white' }} p-4 sm:p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="break-words text-lg font-black text-gray-900">{{ $assignment->title }}</h3>
                                @if($assignment->is_new_assignment)<span class="rounded-full bg-cyan-700 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-white">New</span>@endif
                            </div>
                            <p class="mt-1 text-sm font-semibold text-gray-600">{{ $assignment->subject?->name ?? 'Assignment' }} · {{ $assignment->topic }}</p>
                            <p class="mt-2 text-sm {{ $assignment->due_at && $assignment->due_at->isPast() ? 'font-bold text-rose-700' : 'text-gray-600' }}">
                                @if($assignment->due_at)
                                    Due {{ $assignment->due_at->format('D, j M · g:i A') }}{{ $assignment->due_at->isPast() ? ' · Past due' : '' }}
                                @else
                                    No due date set
                                @endif
                            </p>
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
                <div class="rounded-2xl border border-dashed border-gray-200 p-8 text-center">
                    <p class="font-bold text-gray-800">No assignments yet</p>
                    <p class="mt-1 text-sm text-gray-500">New assignments from your teachers will appear here.</p>
                </div>
            @endforelse
        </div>
    </section>

    <nav aria-label="Learner pages" class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6">
        <a href="{{ route('student.learning.index') }}" class="rounded-2xl border border-cyan-100 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"><span class="text-2xl">📚</span><span class="mt-2 block font-bold text-gray-900">Classwork</span><span class="mt-1 block text-xs text-gray-500">Lessons and quizzes</span></a>
        <a href="{{ route('student.exams.index') }}" class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"><span class="text-2xl">📝</span><span class="mt-2 block font-bold text-gray-900">Exams</span><span class="mt-1 block text-xs text-gray-500">Available exams</span></a>
        <a href="{{ route('student.results.index') }}" class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"><span class="text-2xl">📊</span><span class="mt-2 block font-bold text-gray-900">Results</span><span class="mt-1 block text-xs text-gray-500">Exam history</span></a>
        <a href="{{ route('student.reports.index') }}" class="rounded-2xl border border-amber-100 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"><span class="text-2xl">📄</span><span class="mt-2 block font-bold text-gray-900">Reports</span><span class="mt-1 block text-xs text-gray-500">Report cards</span></a>
        <a href="{{ route('student.developmental-reports.index') }}" class="rounded-2xl border border-teal-100 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"><span class="text-2xl">🌱</span><span class="mt-2 block font-bold text-gray-900">Developmental</span><span class="mt-1 block text-xs text-gray-500">Developmental reports</span></a>
        <a href="{{ route('attendance.my') }}" class="rounded-2xl border border-violet-100 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"><span class="text-2xl">✅</span><span class="mt-2 block font-bold text-gray-900">Attendance</span><span class="mt-1 block text-xs text-gray-500">My attendance</span></a>
    </nav>
</div>
@endsection
