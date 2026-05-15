@extends('layouts.app')

@section('title', 'Grade Exam')

@section('content')
<div class="space-y-6">
    <!-- Add this warning box for in-progress exams -->
    @if($attempt->status === 'in_progress')
    <div class="bg-yellow-50 border-2 border-yellow-300 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-yellow-800 flex items-center gap-2">
                    <span class="text-2xl">⚠️</span> Exam Still In Progress
                </h3>
                <p class="text-sm text-yellow-700 mt-1">
                    This student (<strong>{{ $attempt->student->name }}</strong>) hasn't submitted yet. 
                    You can submit it on their behalf if needed.
                </p>
            </div>
            <form method="POST" 
                  action="{{ route('admin.submit-for-student', $attempt->id) }}" 
                  onsubmit="return confirm('⚠️ Submit this exam for {{ $attempt->student->name }}?\n\nThis will:\n✓ Mark exam as completed\n✓ Auto-grade MCQ/Fill-blank questions\n✓ Stop the timer\n\nThis action cannot be undone!')">
                @csrf
                <button type="submit" 
                        class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-3 rounded-lg font-bold shadow-lg transition">
                    📝 Submit For Student
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- Rest of your existing grading page content continues here -->
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Grade Exam Attempt</h2>
                <p class="text-gray-600">{{ $attempt->exam->title }} - {{ $attempt->exam->subject }}</p>
            </div>
            <a href="{{ route('admin.exam.results', $attempt->exam_id) }}" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                ← Back to Results
            </a>
        </div>

        <!-- Student Info -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded">
            <div>
                <span class="text-sm text-gray-600">Student:</span>
                <p class="font-semibold">{{ $attempt->user->name }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-600">Registration:</span>
                <p class="font-semibold">{{ $attempt->user->registration_number }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-600">Submitted:</span>
                <p class="font-semibold">{{ $attempt->submitted_at->format('d M Y, h:i A') }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-600">Status:</span>
                <p class="font-semibold">
                    @if($attempt->isGraded())
                        <span class="text-green-600">Graded</span>
                    @else
                        <span class="text-yellow-600">Pending</span>
                    @endif
                </p>
            </div>
        </div>

        <!-- Current Score -->
        @if($attempt->total_score !== null)
        <div class="mt-4 bg-blue-50 p-4 rounded">
            <div class="flex justify-between items-center">
                <div>
                    <span class="text-sm text-gray-600">Current Total Score:</span>
                    <p class="text-2xl font-bold text-blue-600">{{ $attempt->total_score }}/{{ $attempt->exam->total_marks }}</p>
                </div>
                <div class="text-right">
                    <span class="text-sm text-gray-600">Breakdown:</span>
                    <p class="text-sm">Objective: {{ $attempt->objective_score ?? 0 }}</p>
                    <p class="text-sm">Subjective: {{ $attempt->subjective_score ?? 0 }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Grading Form -->
    <form action="{{ route('admin.attempt.update-grade', $attempt->id) }}" method="POST">
        @csrf

        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Questions and Answers -->
        @foreach($attempt->answers as $index => $answer)
        @php
            $question = $answer->question;
            $isObjective = $question->isObjective();
        @endphp

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-start mb-4">
                <span class="bg-gray-200 text-gray-700 rounded-full w-8 h-8 flex items-center justify-center font-semibold mr-3 flex-shrink-0">
                    {{ $index + 1 }}
                </span>
                <div class="flex-1">
                    <div class="text-gray-800 font-medium mb-2">{!! $question->formattedQuestionText() !!}</div>

<!-- Show reference image in grading too -->
@if($question->image_path)
<div class="mt-3 mb-3 border rounded-lg overflow-hidden">
    <div class="bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Reference Image</div>
    <div class="p-2 bg-white">
        <img src="{{ $question->getImageUrl() }}" 
             alt="Reference" 
             class="max-h-40 object-contain border border-gray-200 rounded">
    </div>
</div>
@endif
                    <div class="flex gap-2 flex-wrap">
                        <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded">
                            {{ ucwords(str_replace('_', ' ', $question->question_type)) }}
                        </span>
                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                            {{ $question->marks }} {{ $question->marks == 1 ? 'mark' : 'marks' }}
                        </span>
                        @if($isObjective)
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">
                                Auto-graded
                            </span>
                        @else
                            <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">
                                Manual grading required
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="ml-11">
                <!-- Show Question Options (for MCQ) -->
                @if($question->question_type === 'multiple_choice' && $question->options)
                    <div class="mb-3 bg-gray-50 p-3 rounded">
                        <p class="text-sm font-semibold text-gray-700 mb-2">Options:</p>
                        <div class="space-y-1">
                            @foreach($question->options as $key => $option)
                            <p class="text-sm p-2 rounded {{ $key === $question->correct_answer ? 'bg-green-100 text-green-800 font-semibold' : ($key === $answer->answer_text ? 'bg-red-100 text-red-800' : 'text-gray-600') }}">
                                {{ $key }}. {{ $option }}
                                @if($key === $question->correct_answer)
                                    <span class="text-xs ml-2">✓ Correct</span>
                                @endif
                                @if($key === $answer->answer_text && $key !== $question->correct_answer)
                                    <span class="text-xs ml-2">✗ Student's Answer</span>
                                @endif
                            </p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Student's Answer -->
                @if($answer->answer_text)
                    <div class="mb-3 {{ $isObjective ? 'bg-gray-50' : 'bg-blue-50' }} p-4 rounded">
                        <p class="text-sm font-semibold text-gray-700 mb-2">Student's Answer:</p>
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
                    <div class="mb-3 bg-red-50 p-4 rounded border-l-4 border-red-500">
                        <p class="text-sm text-red-800 font-semibold">Not Answered</p>
                    </div>
                @endif

                <!-- Correct Answer (for objective questions) -->
                @if($question->question_type === 'fill_blank' && $question->correct_answer)
                    <div class="mb-3 bg-green-50 p-4 rounded">
                        <p class="text-sm font-semibold text-green-700 mb-2">Correct Answer:</p>
                        <p class="text-green-800">{{ $question->correct_answer }}</p>
                    </div>
                @endif

                <!-- Grading Section -->
                @if($isObjective)
                    <!-- Auto-graded - Show result only -->
                    <div class="bg-gray-100 p-4 rounded">
                        <p class="text-sm font-semibold text-gray-700">Auto-graded Result:</p>
                        <p class="text-lg font-bold {{ $answer->is_correct ? 'text-green-600' : 'text-red-600' }}">
                            {{ $answer->marks_obtained }}/{{ $question->marks }} marks
                            @if($answer->is_correct)
                                ✓ Correct
                            @else
                                ✗ Incorrect
                            @endif
                        </p>
                    </div>
                @else
                    <!-- Manual grading inputs -->
                    <div class="border-t pt-4 mt-4">
                        <input type="hidden" name="grades[{{ $loop->index }}][answer_id]" value="{{ $answer->id }}">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Marks Obtained (Max: {{ $question->marks }}) *
                                </label>
                                <input type="number" 
                                       name="grades[{{ $loop->index }}][marks_obtained]" 
                                       value="{{ old('grades.'.$loop->index.'.marks_obtained', $answer->marks_obtained ?? 0) }}"
                                       min="0" 
                                       max="{{ $question->marks }}" 
                                       step="0.5"
                                       required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Feedback (Optional)
                                </label>
                                <input type="text" 
                                       name="grades[{{ $loop->index }}][feedback]" 
                                       value="{{ old('grades.'.$loop->index.'.feedback', $answer->feedback) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                       placeholder="Good attempt, Well done, etc.">
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endforeach

        <!-- Submit Button -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4">
                <label for="final_score" class="block text-sm font-bold text-amber-900 mb-2">
                    Final Score Override
                </label>
                <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
                    <input type="number"
                           id="final_score"
                           name="final_score"
                           value="{{ old('final_score', $attempt->total_score) }}"
                           min="0"
                           max="{{ max((float) $attempt->exam->total_marks, 1) }}"
                           step="0.5"
                           class="w-full sm:w-48 px-4 py-2 border border-amber-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                    <span class="text-sm text-amber-800">
                        Use this only when a computer-graded score needs correction. Maximum: {{ number_format($attempt->exam->total_marks, 1) }}.
                    </span>
                </div>
            </div>

            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    @php
                        $subjectiveQuestions = $attempt->answers->filter(function($a) {
                            return !$a->question->isObjective();
                        })->count();
                    @endphp
                    <p>Total Questions: {{ $attempt->answers->count() }}</p>
                    <p>Auto-graded: {{ $attempt->answers->count() - $subjectiveQuestions }}</p>
                    <p>Manual Grading Required: {{ $subjectiveQuestions }}</p>
                </div>
                
                <div class="flex gap-4">
                    <a href="{{ route('admin.results.index') }}" 
                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-8 py-3 rounded-lg font-semibold">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold">
                        {{ $attempt->isGraded() ? 'Update Grades' : 'Complete Grading' }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
