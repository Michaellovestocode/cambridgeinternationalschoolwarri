@extends('layouts.app')

@section('title', 'Exam Result')

@section('content')
<div class="space-y-6">
    @if(!$canViewReleasedResults)
    <div class="bg-white rounded-lg shadow p-8 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-green-100 text-green-700 flex items-center justify-center mb-4">
            <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Exam Submitted Successfully</h2>
        <p class="text-gray-600">{{ $attempt->exam->title }} has been submitted.</p>
        <p class="text-sm text-gray-500 mt-2">Your score and script will appear here only after your teacher releases the result.</p>
        <div class="mt-6">
            <a href="{{ route('student.dashboard') }}"
               class="inline-block bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold transition">
                Back to Dashboard
            </a>
        </div>
    </div>
    @else
    <!-- Result Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">{{ $attempt->exam->title }}</h2>
            <p class="text-gray-600">{{ $attempt->exam->subject }}</p>







            @if($attempt->isGraded() || ($attempt->objective_score !== null && $attempt->objective_score > 0))
    <!-- Score Display -->
    <div class="mt-6 mb-4">
        <div class="inline-block {{ $attempt->total_score >= $attempt->exam->pass_mark ? 'bg-green-100' : 'bg-red-100' }} rounded-full px-8 py-4">
            <div class="text-5xl font-bold {{ $attempt->total_score >= $attempt->exam->pass_mark ? 'text-green-600' : 'text-red-600' }}">
                {{ $attempt->total_score }}<span class="text-2xl">/{{ $attempt->exam->total_marks }}</span>
            </div>
            @if(!$attempt->isGraded())
                <div class="text-sm mt-2 text-orange-800">
                    ⏳ Partial Result (Coding questions pending manual grading)
                </div>
            @else
                <div class="text-sm mt-2 {{ $attempt->total_score >= $attempt->exam->pass_mark ? 'text-green-800' : 'text-red-800' }}">
                    @if($attempt->total_score >= $attempt->exam->pass_mark)
                        ✓ Passed (Pass mark: {{ $attempt->exam->pass_mark }})
                    @else
                        ✗ Failed (Pass mark: {{ $attempt->exam->pass_mark }})
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Score Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
        <div class="bg-blue-50 p-4 rounded-lg {{ $attempt->isGraded() ? '' : 'border-2 border-green-500' }}">
            <div class="text-sm text-gray-600">Objective Score</div>
            <div class="text-2xl font-bold text-blue-600">
                {{ $attempt->objective_score ?? 0 }}<span class="text-lg text-blue-400">/{{ $objectiveTotal }}</span>
            </div>
            @if(!$attempt->isGraded())
                <div class="text-xs text-green-600 mt-1 font-semibold">✅ Auto-graded</div>
            @endif
        </div>
        <div class="bg-purple-50 p-4 rounded-lg {{ $attempt->isGraded() ? '' : 'border-2 border-orange-500' }}">
            <div class="text-sm text-gray-600">Subjective Score</div>
            <div class="text-2xl font-bold text-purple-600">
                {{ $attempt->subjective_score ?? 0 }}<span class="text-lg text-purple-400">/{{ $subjectiveTotal }}</span>
            </div>
            @if(!$attempt->isGraded())
                <div class="text-xs text-orange-600 mt-1 font-semibold">⏳ Awaiting teacher grading</div>
            @endif
        </div>
        <div class="bg-gray-50 p-4 rounded-lg">
            <div class="text-sm text-gray-600">Percentage</div>
            <div class="text-2xl font-bold text-gray-800">
                @php
                    $percentage = $attempt->exam->total_marks > 0 
                        ? round(($attempt->total_score / $attempt->exam->total_marks) * 100, 1) 
                        : 0;
                @endphp
                {{ $percentage }}%
            </div>
            @if(!$attempt->isGraded())
                <div class="text-xs text-gray-600 mt-1">Partial percentage</div>
            @endif
        </div>
    </div>
@else
    <!-- No Scores Yet (All Subjective) -->
    <div class="mt-6 bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded-lg">
        <p class="font-semibold">⏳ Grading in Progress</p>
        <p class="text-sm mt-1">Your exam has been submitted and is awaiting manual grading by your teacher.</p>
    </div>
@endif

            
           






        </div>
    </div>

    <!-- Exam Details -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Exam Details</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-gray-600">Submitted:</span>
                <p class="font-semibold">{{ $attempt->submitted_at->format('d M Y, h:i A') }}</p>
            </div>
            <div>
                <span class="text-gray-600">Duration:</span>
                <p class="font-semibold">{{ $attempt->exam->duration_minutes }} minutes</p>
            </div>
            <div>
                <span class="text-gray-600">Total Questions:</span>
                <p class="font-semibold">{{ $attempt->exam->questions->count() }}</p>
            </div>
           <div>
    <span class="text-gray-600">Status:</span>
    <p class="font-semibold">
        @if($attempt->isGraded())
            <span class="text-green-600">Graded</span>
        @else
            <span class="text-yellow-600">Pending</span>
        @endif
    </p>
</div>
        </div>
    </div>

    @if($attempt->isGraded())
    <!-- Answer Review -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Answer Review</h3>
        
        @foreach($attempt->answers as $index => $answer)
        @php
            $question = $answer->question;
        @endphp
        <div class="border-b pb-6 mb-6 last:border-b-0 last:pb-0 last:mb-0">
            <div class="flex items-start mb-3">
                <span class="bg-gray-200 text-gray-700 rounded-full w-8 h-8 flex items-center justify-center font-semibold mr-3 flex-shrink-0">
                    {{ $index + 1 }}
                </span>
                <div class="flex-1">
                    <div class="text-gray-800 font-medium">{!! $question->formattedQuestionText() !!}</div>

<!-- Show reference image in results too -->
@if($question->image_path)
<div class="mt-3 border rounded-lg overflow-hidden">
    <div class="bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Reference Image</div>
    <div class="p-2 bg-white">
        <img src="{{ $question->getImageUrl() }}" 
             alt="Reference" 
             class="max-h-40 object-contain border border-gray-200 rounded">
    </div>
</div>
@endif
                    <div class="mt-2 flex items-center gap-2 flex-wrap">
                        <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded">
                            {{ ucwords(str_replace('_', ' ', $question->question_type)) }}
                        </span>
                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                            {{ $question->marks }} {{ $question->marks == 1 ? 'mark' : 'marks' }}
                        </span>
                        @if($answer->marks_obtained !== null)
                            <span class="text-xs {{ $answer->is_correct || $answer->marks_obtained > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} px-2 py-1 rounded font-semibold">
                                Scored: {{ $answer->marks_obtained }}/{{ $question->marks }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="ml-11">
                <!-- Show Question Options (for MCQ) -->
                @if($question->question_type === 'multiple_choice' && $question->options)
                    <div class="mb-3 space-y-1">
                        @foreach($question->options as $key => $option)
                        <p class="text-sm p-2 rounded {{ $key === $question->correct_answer ? 'bg-green-50 text-green-800 font-semibold' : ($key === $answer->answer_text ? 'bg-red-50 text-red-800' : 'text-gray-600') }}">
                            {{ $key }}. {{ $option }}
                            @if($key === $question->correct_answer)
                                <span class="text-xs ml-2">✓ Correct Answer</span>
                            @endif
                            @if($key === $answer->answer_text && $key !== $question->correct_answer)
                                <span class="text-xs ml-2">✗ Your Answer</span>
                            @endif
                        </p>
                        @endforeach
                    </div>
                @endif

                <!-- Student's Answer -->
                @if($answer->answer_text)
                    <div class="bg-gray-50 p-4 rounded-lg mb-3">
                        <p class="text-sm font-semibold text-gray-700 mb-1">Your Answer:</p>
                        @if($question->question_type === 'multiple_choice')
                            <p class="text-gray-800">
                                <strong>{{ $answer->answer_text }}.</strong> 
                                {{ $question->options[$answer->answer_text] ?? 'N/A' }}
                            </p>
                        @elseif($question->question_type === 'coding')
                            <pre class="bg-gray-800 text-gray-200 p-3 rounded font-mono text-sm overflow-x-auto">{{ $answer->answer_text }}</pre>
                        @else
                            <p class="text-gray-800 whitespace-pre-wrap">{{ $answer->answer_text }}</p>
                        @endif
                    </div>
                @else
                    <div class="bg-red-50 p-4 rounded-lg mb-3">
                        <p class="text-sm text-red-800">Not Answered</p>
                    </div>
                @endif

                <!-- Correct Answer (for objective questions) -->
                @if($question->question_type === 'fill_blank' && $question->correct_answer)
                    <div class="bg-green-50 p-4 rounded-lg mb-3">
                        <p class="text-sm font-semibold text-green-700 mb-1">Correct Answer:</p>
                        <p class="text-green-800">{{ $question->correct_answer }}</p>
                    </div>
                @endif

                <!-- Teacher's Feedback -->
                @if($answer->feedback)
                    <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                        <p class="text-sm font-semibold text-blue-700 mb-1">Teacher's Feedback:</p>
                        <p class="text-blue-800">{{ $answer->feedback }}</p>
                    </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Action Buttons -->
    <!-- Action Buttons -->
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex flex-wrap gap-4 justify-center">
        <a href="{{ route('student.dashboard') }}" 
           class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold transition">
            ← Back to Dashboard
        </a>
    </div>
</div>
    @endif
</div>

@push('styles')
<style>
    @media print {
        nav, .no-print, button {
            display: none !important;
        }
        body {
            background: white;
        }
    }
</style>
@endpush
@endsection
