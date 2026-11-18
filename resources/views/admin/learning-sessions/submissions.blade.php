@extends('layouts.app')

@section('title', 'Learning Submissions')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800" role="status">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-4 rounded-2xl bg-white p-5 shadow sm:flex-row sm:items-center sm:justify-between sm:p-6">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide text-cyan-700">Learning Hub</p>
            <h1 class="mt-1 text-2xl font-black text-gray-900">{{ $learningSession->title }} Submissions</h1>
            <p class="text-sm text-gray-600">{{ $classes->pluck('display_name')->join(', ') ?: 'No class assigned' }} · {{ $learningSession->subject->name }}</p>
        </div>
        <a href="{{ route('admin.learning-sessions.edit', $learningSession) }}" class="rounded-xl bg-gray-100 px-4 py-3 text-center text-sm font-bold text-gray-700">Back to Topic</a>
    </div>

    <section class="grid grid-cols-1 gap-3 sm:grid-cols-3" aria-label="Submission summary">
        <div class="rounded-2xl bg-white p-4 shadow-sm"><p class="text-xs font-bold uppercase tracking-wide text-gray-500">Learners assigned</p><p class="mt-1 text-2xl font-black text-gray-900">{{ $submissionStats['assigned'] }}</p></div>
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4"><p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Attempted</p><p class="mt-1 text-2xl font-black text-emerald-900">{{ $submissionStats['attempted'] }}</p></div>
        <div class="rounded-2xl border border-rose-100 bg-rose-50 p-4"><p class="text-xs font-bold uppercase tracking-wide text-rose-700">Not attempted</p><p class="mt-1 text-2xl font-black text-rose-900">{{ $submissionStats['not_attempted'] }}</p></div>
    </section>

    <form method="GET" class="grid gap-3 rounded-2xl bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-[1fr_220px_220px_auto] lg:items-end">
        <label class="text-xs font-bold text-gray-600">Search learner<input type="search" name="search" value="{{ $search }}" placeholder="Name or registration number" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-3 text-sm"></label>
        <label class="text-xs font-bold text-gray-600">Class<select name="class_id" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-3 text-sm"><option value="">All classes</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected($classId == $class->id)>{{ $class->display_name }}</option>@endforeach</select></label>
        <label class="text-xs font-bold text-gray-600">Submission status<select name="status" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-3 text-sm"><option value="">All learners</option><option value="attempted" @selected($status === 'attempted')>Attempted</option><option value="not_attempted" @selected($status === 'not_attempted')>Not attempted</option></select></label>
        <button class="rounded-xl bg-cyan-700 px-5 py-3 text-sm font-bold text-white">Filter</button>
    </form>

    <div class="overflow-x-auto rounded-2xl bg-white shadow">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-950 text-xs uppercase text-white">
                <tr><th class="px-4 py-3">Student</th><th class="px-4 py-3">Submitted</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Score</th><th class="px-4 py-3">Action</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($learners as $learner)
                    @php($attempt = $attemptsByLearner->get($learner->id))
                    @php($objectiveAnswers = $attempt?->answers->filter(fn ($answer) => $answer->question?->question_type === 'objective') ?? collect())
                    @php($objectiveScore = $objectiveAnswers->sum(fn ($answer) => $answer->teacher_score !== null ? (float) $answer->teacher_score : ($answer->is_correct ? 1 : 0)))
                    @php($objectiveScoreDisplay = fmod($objectiveScore, 1) === 0.0 ? (int) $objectiveScore : rtrim(rtrim(number_format($objectiveScore, 2, '.', ''), '0'), '.'))
                    <tr>
                        <td class="px-4 py-4 font-bold text-gray-900">{{ $learner->name }}<span class="mt-1 block text-xs font-normal text-gray-500">{{ $learner->registration_number ?: 'No admission number' }} · {{ $learner->class?->display_name ?? 'No class' }}</span></td>
                        <td class="px-4 py-4 text-gray-600">{{ $attempt?->completed_at?->format('j M Y, g:i A') ?: '-' }}</td>
                        <td class="px-4 py-4">
                            @if(! $attempt)
                                <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-800">Not attempted</span>
                            @else
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $attempt->is_published ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $attempt->is_published ? 'Published' : 'Needs review' }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 font-bold text-cyan-700">
                            @if(! $attempt)
                                -
                            @elseif($attempt->is_published)
                                {{ $attempt->score }}/{{ $attempt->total_questions }}
                            @elseif($objectiveAnswers->isNotEmpty())
                                {{ $objectiveScoreDisplay }}/{{ $objectiveAnswers->count() }}
                                <span class="block text-xs font-medium text-gray-500">Objective · computer marked</span>
                            @else
                                Pending
                            @endif
                        </td>
                        <td class="px-4 py-4">@if($attempt)<a href="{{ route('admin.learning-sessions.attempts.grade', $attempt) }}" class="rounded-lg bg-cyan-600 px-3 py-2 text-xs font-bold text-white">View Submission</a>@else<span class="text-xs font-semibold text-gray-400">Awaiting learner</span>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-gray-500">No learners match this filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
