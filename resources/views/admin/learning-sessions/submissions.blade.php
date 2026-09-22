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
            <p class="text-sm text-gray-600">{{ $learningSession->schoolClass->display_name }} · {{ $learningSession->subject->name }}</p>
        </div>
        <a href="{{ route('admin.learning-sessions.edit', $learningSession) }}" class="rounded-xl bg-gray-100 px-4 py-3 text-center text-sm font-bold text-gray-700">Back to Topic</a>
    </div>

    <div class="overflow-x-auto rounded-2xl bg-white shadow">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-950 text-xs uppercase text-white">
                <tr><th class="px-4 py-3">Student</th><th class="px-4 py-3">Submitted</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Score</th><th class="px-4 py-3">Action</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($learners as $learner)
                    @php($attempt = $attemptsByLearner->get($learner->id))
                    <tr>
                        <td class="px-4 py-4 font-bold text-gray-900">{{ $learner->name }}</td>
                        <td class="px-4 py-4 text-gray-600">{{ $attempt?->completed_at?->format('j M Y, g:i A') ?: '-' }}</td>
                        <td class="px-4 py-4">
                            @if(! $attempt)
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700">Not submitted</span>
                            @else
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $attempt->is_published ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $attempt->is_published ? 'Published' : 'Needs review' }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 font-bold text-cyan-700">{{ $attempt?->is_published ? $attempt->score . '/' . $attempt->total_questions : ($attempt ? 'Pending' : '-') }}</td>
                        <td class="px-4 py-4">@if($attempt)<a href="{{ route('admin.learning-sessions.attempts.grade', $attempt) }}" class="rounded-lg bg-cyan-600 px-3 py-2 text-xs font-bold text-white">Review and Score</a>@else<span class="text-xs font-semibold text-gray-400">Awaiting learner</span>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-gray-500">No learners are assigned to this activity.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
