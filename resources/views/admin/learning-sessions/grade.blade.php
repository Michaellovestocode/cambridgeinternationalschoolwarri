@extends('layouts.app')

@section('title', 'Grade Learning Submission')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-col gap-4 rounded-2xl bg-white p-5 shadow sm:flex-row sm:items-center sm:justify-between sm:p-6">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide text-cyan-700">Review Submission</p>
            <h1 class="mt-1 text-2xl font-black text-gray-900">{{ $attempt->user->name }}</h1>
            <p class="text-sm text-gray-600">{{ $attempt->learningSession->title }} · Submitted {{ $attempt->completed_at?->format('j M Y, g:i A') }}</p>
        </div>
        <a href="{{ route('admin.learning-sessions.submissions', $attempt->learningSession) }}" class="rounded-xl bg-gray-100 px-4 py-3 text-center text-sm font-bold text-gray-700">Back to Submissions</a>
    </div>

    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.learning-sessions.attempts.update', $attempt) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')
        @foreach($attempt->answers as $answer)
            @php($question = $answer->question)
            <div class="rounded-2xl bg-white p-5 shadow">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Question {{ $loop->iteration }} · {{ ucfirst($question->question_type) }}</p>
                        <h2 class="mt-1 font-bold text-gray-900">{{ $question->question_text }}</h2>
                    </div>
                    @if($question->question_type === 'objective')
                        <span class="shrink-0 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">Computer scored: {{ $answer->is_correct ? 'Correct' : 'Review' }}</span>
                    @else
                        <span class="shrink-0 rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">Teacher score / {{ $question->marks ?: 1 }}</span>
                    @endif
                </div>
                <div class="mt-4 rounded-xl border border-gray-100 bg-gray-50 p-4 whitespace-pre-line text-sm text-gray-800">{{ $answer->selected_option ?: 'No answer submitted.' }}</div>

                @if($question->question_type === 'theory')
                    <div class="mt-4 grid gap-4 sm:grid-cols-[180px_1fr]">
                        <div>
                            <label class="mb-1 block text-xs font-bold text-gray-700">Score</label>
                            <input type="number" name="answers[{{ $answer->id }}][teacher_score]" value="{{ $answer->teacher_score }}" min="0" max="{{ $question->marks ?: 1 }}" step="0.01" class="w-full rounded-xl border border-gray-200 px-3 py-3">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-gray-700">Feedback</label>
                            <textarea name="answers[{{ $answer->id }}][teacher_feedback]" rows="3" class="w-full rounded-xl border border-gray-200 px-3 py-3" placeholder="Tell the student what was strong or what to improve.">{{ $answer->teacher_feedback }}</textarea>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach

        <div class="sticky bottom-3 flex flex-col gap-3 rounded-2xl bg-white/95 p-4 shadow-lg backdrop-blur sm:flex-row sm:justify-end">
            <button name="publish" value="0" class="rounded-xl bg-gray-200 px-5 py-3 text-sm font-bold text-gray-800">Save Draft</button>
            <button name="publish" value="1" class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white">Save and Publish Result</button>
        </div>
    </form>
</div>
@endsection
