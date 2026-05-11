@extends('layouts.app')

@section('title', 'Learning Result')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-lg p-8">
        <div class="flex flex-wrap justify-between items-center gap-6">
            <div>
                <p class="text-cyan-700 font-semibold">{{ $attempt->learningSession->subject->name ?? 'Subject' }}</p>
                <h1 class="text-3xl font-bold text-gray-900 mt-1">{{ $attempt->learningSession->title }}</h1>
                <p class="text-gray-600 mt-2">Completed {{ $attempt->completed_at?->format('d M Y, h:i A') }}</p>
            </div>
            <div class="text-center bg-cyan-50 rounded-2xl px-8 py-5">
                <div class="text-4xl font-black text-cyan-700">{{ $attempt->percentage() }}%</div>
                <div class="text-sm text-gray-600 mt-1">{{ $attempt->score }}/{{ $attempt->total_questions }} correct</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Corrections</h2>
        <div class="space-y-5">
            @foreach($attempt->answers as $answer)
            @php($question = $answer->question)
            <div class="border rounded-xl p-5 {{ $answer->is_correct ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                <div class="flex flex-wrap justify-between gap-3 mb-4">
                    <p class="font-bold text-gray-900">{{ $loop->iteration }}. {{ $question->question_text }}</p>
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $answer->is_correct ? 'bg-green-600 text-white' : 'bg-red-600 text-white' }}">
                        {{ $answer->is_correct ? 'Correct' : 'Review' }}
                    </span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    @foreach($question->options as $key => $option)
                    <div class="rounded-lg px-4 py-3 {{ $key === $question->correct_option ? 'bg-white border-2 border-green-500 text-green-900' : ($key === $answer->selected_option ? 'bg-white border-2 border-red-400 text-red-900' : 'bg-white border border-gray-100 text-gray-700') }}">
                        <strong>{{ $key }}.</strong> {{ $option }}
                        @if($key === $question->correct_option)
                            <span class="font-bold"> - Correct answer</span>
                        @elseif($key === $answer->selected_option)
                            <span class="font-bold"> - Your answer</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @if(!$answer->selected_option)
                    <p class="mt-3 text-sm text-red-700 font-semibold">You did not answer this question.</p>
                @endif
                @if($question->explanation)
                    <p class="mt-4 text-sm text-gray-700"><strong>Explanation:</strong> {{ $question->explanation }}</p>
                @endif
            </div>
            @endforeach
        </div>

        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('student.learning.show', $attempt->learningSession) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl font-bold">Retake Session</a>
            <a href="{{ route('student.learning.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-6 py-3 rounded-xl font-bold">More Sessions</a>
        </div>
    </div>
</div>
@endsection
