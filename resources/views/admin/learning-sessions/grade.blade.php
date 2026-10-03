@extends('layouts.app')

@section('title', 'Review Learning Submission')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-col gap-4 rounded-2xl bg-white p-5 shadow sm:flex-row sm:items-center sm:justify-between sm:p-6">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide text-cyan-700">Review Submission</p>
            <h1 class="mt-1 text-2xl font-black text-gray-900">{{ $attempt->user->name }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $attempt->learningSession->title }} | {{ $attempt->learningSession->subject->name ?? 'No subject' }} | Submitted {{ $attempt->completed_at?->format('j M Y, g:i A') }}</p>
        </div>
        <a href="{{ route('admin.learning-sessions.submissions', $attempt->learningSession) }}" class="rounded-xl bg-gray-100 px-4 py-3 text-center text-sm font-bold text-gray-700">Back to Submissions</a>
    </div>

    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800" role="status">{{ session('success') }}</div>
    @endif

    <section class="grid gap-4 sm:grid-cols-3" aria-label="Submission summary">
        <div class="rounded-2xl border border-cyan-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-gray-500">Objective questions</p>
            <p class="mt-1 text-2xl font-black text-gray-900">{{ $objectiveCorrectCount }} / {{ $objectiveAnswers->count() }}
                @if($objectivePercentage !== null)<span class="ml-1 text-base text-cyan-700">{{ $objectivePercentage }}%</span>@endif</p>
            <p class="mt-1 text-xs text-gray-500">Computer scored</p>
        </div>
        <div class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-gray-500">Written responses</p>
            <p class="mt-1 text-2xl font-black text-gray-900">{{ $writtenAnswers->count() }}</p>
            <p class="mt-1 text-xs text-gray-500">Marked by the teacher</p>
        </div>
        <div class="rounded-2xl border {{ $unmarkedWrittenCount ? 'border-amber-200 bg-amber-50' : 'border-emerald-100 bg-white' }} p-5 shadow-sm">
            <p class="text-sm font-semibold text-gray-500">Written responses to mark</p>
            <p class="mt-1 text-2xl font-black {{ $unmarkedWrittenCount ? 'text-amber-800' : 'text-emerald-800' }}">{{ $unmarkedWrittenCount }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ $unmarkedWrittenCount ? 'Enter a score and feedback below.' : 'All written responses are marked.' }}</p>
        </div>
    </section>

    <form action="{{ route('admin.learning-sessions.attempts.update', $attempt) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        @if($objectiveAnswers->isNotEmpty())
        <section class="space-y-4">
            <div>
                <h2 class="text-xl font-black text-gray-900">Objective answers</h2>
                <p class="text-sm text-gray-600">Review the learner's selection against the correct answer.</p>
            </div>
            @foreach($objectiveAnswers as $answer)
                @php($question = $answer->question)
                @if(! $question)
                    @continue
                @endif
                <article class="rounded-2xl bg-white p-5 shadow">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Question {{ $attempt->answers->search($answer) + 1 }} | Objective</p>
                            <h3 class="mt-1 font-bold text-gray-900">{{ $question->question_text }}</h3>
                        </div>
                        <span class="shrink-0 rounded-full px-3 py-1 text-xs font-bold {{ $answer->is_correct ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">{{ $answer->is_correct ? 'Computer scored: Correct' : 'Computer scored: Incorrect' }}</span>
                    </div>

                    @if(is_array($question->options) && count($question->options))
                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                            @foreach($question->options as $key => $option)
                                @php($isSelected = strtoupper((string) $answer->selected_option) === strtoupper((string) $key))
                                @php($isCorrect = strtoupper((string) $question->correct_option) === strtoupper((string) $key))
                                <div class="rounded-xl border px-4 py-3 text-sm {{ $isCorrect ? 'border-emerald-300 bg-emerald-50 text-emerald-900' : ($isSelected ? 'border-rose-300 bg-rose-50 text-rose-900' : 'border-gray-200 bg-gray-50 text-gray-700') }}">
                                    <strong>{{ $key }}.</strong> {{ $option }}
                                    @if($isSelected)<span class="ml-1 font-bold"> - Student's answer</span>@endif
                                    @if($isCorrect)<span class="ml-1 font-bold"> - Correct answer</span>@endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="mt-4 rounded-xl border border-gray-100 bg-gray-50 p-4 text-sm text-gray-800">
                            <span class="font-bold">Student's answer:</span> {{ $answer->selected_option ?: 'No answer submitted.' }}
                            @if($question->correct_option)<span class="ml-3 font-bold text-emerald-800">Correct answer: {{ $question->correct_option }}</span>@endif
                        </div>
                    @endif
                    @if($question->explanation)
                        <p class="mt-4 rounded-xl bg-cyan-50 p-4 text-sm text-cyan-950"><strong>Explanation:</strong> {{ $question->explanation }}</p>
                    @endif
                </article>
            @endforeach
        </section>
        @endif

        @if($writtenAnswers->isNotEmpty())
        <section class="space-y-4">
            <div>
                <h2 class="text-xl font-black text-gray-900">Written answers | Teacher review</h2>
                <p class="text-sm text-gray-600">Award a score and leave feedback for each written response.</p>
            </div>
            @foreach($writtenAnswers as $answer)
                @php($question = $answer->question)
                @if(! $question)
                    @continue
                @endif
                <article class="rounded-2xl border border-violet-100 bg-white p-5 shadow">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wide text-violet-700">Question {{ $attempt->answers->search($answer) + 1 }} | Written | {{ $question->marks ?: 1 }} {{ (float) ($question->marks ?: 1) === 1.0 ? 'mark' : 'marks' }}</p>
                            <h3 class="mt-1 font-bold text-gray-900">{{ $question->question_text }}</h3>
                        </div>
                        <span class="shrink-0 rounded-full px-3 py-1 text-xs font-bold {{ $answer->teacher_score !== null ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $answer->teacher_score !== null ? 'Marked | ' . $answer->teacher_score . ' / ' . ($question->marks ?: 1) : 'Needs marking' }}</span>
                    </div>
                    <div class="mt-4 whitespace-pre-line rounded-xl border border-gray-100 bg-gray-50 p-4 text-sm text-gray-800">{{ $answer->selected_option ?: 'No answer submitted.' }}</div>
                    @if($question->explanation)
                        <p class="mt-3 rounded-xl bg-cyan-50 p-4 text-sm text-cyan-950"><strong>Marking guide:</strong> {{ $question->explanation }}</p>
                    @endif
                    @if(!empty($answer->handwriting_pages))
                        <div class="mt-4 rounded-xl border border-violet-200 bg-violet-50 p-4">
                            <p class="mb-3 text-xs font-bold uppercase tracking-wide text-violet-800">Handwritten workings ({{ count($answer->handwriting_pages) }} page{{ count($answer->handwriting_pages) === 1 ? '' : 's' }})</p>
                            <div class="space-y-3">
                                @foreach($answer->handwriting_pages as $page)
                                    <a href="{{ asset('storage/' . $page) }}" target="_blank" rel="noopener" class="block overflow-hidden rounded-lg border border-violet-100 bg-white">
                                        <img src="{{ asset('storage/' . $page) }}" alt="Handwritten answer page {{ $loop->iteration }}" class="h-auto w-full">
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <div class="mt-4 grid gap-4 sm:grid-cols-[180px_1fr]">
                        <div>
                            <label for="teacher-score-{{ $answer->id }}" class="mb-1 block text-xs font-bold text-gray-700">Score (max {{ $question->marks ?: 1 }})</label>
                            <input id="teacher-score-{{ $answer->id }}" type="number" name="answers[{{ $answer->id }}][teacher_score]" value="{{ $answer->teacher_score }}" min="0" max="{{ $question->marks ?: 1 }}" step="0.01" class="w-full rounded-xl border border-gray-200 px-3 py-3">
                        </div>
                        <div>
                            <label for="teacher-feedback-{{ $answer->id }}" class="mb-1 block text-xs font-bold text-gray-700">Feedback for student</label>
                            <textarea id="teacher-feedback-{{ $answer->id }}" name="answers[{{ $answer->id }}][teacher_feedback]" rows="3" class="w-full rounded-xl border border-gray-200 px-3 py-3" placeholder="Explain what was strong or what to improve.">{{ $answer->teacher_feedback }}</textarea>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
        @endif

        <div class="sticky bottom-3 flex flex-col gap-4 rounded-2xl bg-white/95 p-4 shadow-lg backdrop-blur sm:flex-row sm:items-center">
            <div class="mr-auto space-y-3">
                <label class="inline-flex items-start gap-3 text-sm font-semibold text-gray-800">
                    <input type="checkbox" name="allow_resubmission" value="1" @checked($attempt->allow_resubmission) class="mt-1 rounded border-gray-300">
                    <span>Allow this student to submit again
                        <span class="mt-1 block text-xs font-normal text-gray-500">If enabled when you publish, the student can start another attempt after seeing this result.</span>
                    </span>
                </label>
                <p class="max-w-2xl text-xs text-gray-500">Publishing shares the score and correct objective answers with the student, along with any written feedback you add.</p>
            </div>
            <button name="publish" value="0" class="rounded-xl bg-gray-200 px-5 py-3 text-sm font-bold text-gray-800">Save Draft</button>
            <button name="publish" value="1" class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white">Publish Result to Student</button>
        </div>
    </form>
</div>
@endsection
