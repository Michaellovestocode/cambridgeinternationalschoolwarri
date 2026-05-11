@extends('layouts.app')

@section('title', 'Preview In-Progress Exam')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Preview: {{ $attempt->exam->title }}</h2>
                <p class="text-gray-600">Student: <strong>{{ $attempt->user->name }}</strong></p>
                <p class="text-sm text-gray-500 mt-1">Status: In Progress (Not Submitted Yet)</p>
            </div>
            <div class="flex gap-3">
                <form method="POST" 
                      action="{{ route('admin.submit-for-student', $attempt->id) }}" 
                      onsubmit="return confirm('⚠️ Submit exam for {{ $attempt->user->name }}?\n\nThis will:\n✓ Mark as completed\n✓ Auto-grade MCQ/Fill-blank\n✓ Stop timer\n\nCannot be undone!')"
                      class="inline">
                    @csrf
                    <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-bold">
                        ✓ Submit For Student
                    </button>
                </form>
                <a href="{{ route('admin.exam.results', $attempt->exam->id) }}" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-3 rounded-lg font-semibold">
                    ← Back to Results
                </a>
            </div>
        </div>
    </div>

    <!-- Warning Banner -->
    <div class="bg-yellow-50 border-2 border-yellow-300 rounded-lg p-4">
        <div class="flex items-center gap-3">
            <span class="text-3xl">⚠️</span>
            <div>
                <h3 class="font-bold text-yellow-800">Exam Still In Progress</h3>
                <p class="text-sm text-yellow-700">
                    This student is currently taking the exam or hasn't submitted yet. 
                    You can view their current answers below (read-only). 
                    To grade this exam, you must submit it first.
                </p>
            </div>
        </div>
    </div>

    <!-- Questions Preview (Read-Only) -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-xl font-bold mb-6 text-gray-800">Current Answers (Read-Only Preview)</h3>

        <div class="space-y-8">
            @foreach($questions as $index => $question)
            @php
                $answer = $attempt->answers->where('question_id', $question->id)->first();
            @endphp
            
            <div class="border-b pb-6 last:border-b-0">
                <!-- Question Header -->
                <div class="flex items-start mb-4">
                    <span class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-semibold mr-3">
                        {{ $index + 1 }}
                    </span>
                    <div class="flex-1">
                        <div class="text-gray-800 font-medium mb-2">{!! $question->formattedQuestionText() !!}</div>
                        <div class="flex gap-2">
                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                {{ $question->marks }} marks
                            </span>
                            <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded">
                                {{ ucwords(str_replace('_', ' ', $question->question_type)) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Student's Answer -->
                <div class="ml-11 bg-gray-50 rounded-lg p-4">
                    @if($answer)
                        @if($question->question_type === 'multiple_choice')
                            <p class="text-sm text-gray-600 mb-2">Student's Answer:</p>
                            <div class="font-semibold text-gray-800">{{ $answer->answer_text }}</div>
                            
                        @elseif($question->question_type === 'fill_blank')
                            <p class="text-sm text-gray-600 mb-2">Student's Answer:</p>
                            <div class="font-semibold text-gray-800">{{ $answer->answer_text }}</div>
                            
                        @elseif($question->question_type === 'theory')
                            <p class="text-sm text-gray-600 mb-2">Student's Answer:</p>
                            <div class="bg-white border rounded p-3 whitespace-pre-wrap">{{ $answer->answer_text }}</div>
                            
                        @elseif($question->question_type === 'coding')
                            @php
                                $files = json_decode($answer->answer_text, true);
                                if (!$files) {
                                    $files = [
                                        'index.html' => $answer->answer_text ?? '',
                                        'styles.css' => '',
                                        'script.js' => ''
                                    ];
                                }
                            @endphp
                            
                            <p class="text-sm text-gray-600 mb-3">Student's Code:</p>
                            
                            <!-- HTML File -->
                            @if(!empty($files['index.html']))
                            <div class="mb-3">
                                <div class="bg-green-100 text-green-800 px-3 py-1 text-xs font-semibold rounded-t">
                                    📄 HTML File
                                </div>
                                <pre class="bg-gray-900 text-green-400 p-4 rounded-b overflow-x-auto text-sm"><code>{{ $files['index.html'] }}</code></pre>
                            </div>
                            @endif
                            
                            <!-- CSS File -->
                            @if(!empty($files['styles.css']))
                            <div class="mb-3">
                                <div class="bg-blue-100 text-blue-800 px-3 py-1 text-xs font-semibold rounded-t">
                                    🎨 CSS File
                                </div>
                                <pre class="bg-gray-900 text-blue-400 p-4 rounded-b overflow-x-auto text-sm"><code>{{ $files['styles.css'] }}</code></pre>
                            </div>
                            @endif
                            
                            <!-- JS File -->
                            @if(!empty($files['script.js']))
                            <div class="mb-3">
                                <div class="bg-yellow-100 text-yellow-800 px-3 py-1 text-xs font-semibold rounded-t">
                                    ⚡ JavaScript File
                                </div>
                                <pre class="bg-gray-900 text-yellow-400 p-4 rounded-b overflow-x-auto text-sm"><code>{{ $files['script.js'] }}</code></pre>
                            </div>
                            @endif
                            
                            <!-- Live Preview -->
                            <div class="mt-4">
                                <div class="bg-purple-100 text-purple-800 px-3 py-1 text-xs font-semibold rounded-t">
                                    👁️ Live Preview
                                </div>
                                <iframe srcdoc="{{ htmlspecialchars($files['index.html'] ?? '') }}" 
                                        class="w-full h-96 border rounded-b bg-white"
                                        sandbox="allow-scripts allow-same-origin">
                                </iframe>
                            </div>
                        @endif
                    @else
                        <p class="text-gray-400 italic">No answer yet</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Submit Button at Bottom -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <p class="text-gray-600">Ready to grade this exam?</p>
            <form method="POST" 
                  action="{{ route('admin.submit-for-student', $attempt->id) }}" 
                  onsubmit="return confirm('Submit exam for {{ $attempt->user->name }}?')"
                  class="inline">
                @csrf
                <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-bold">
                    ✓ Submit For Student & Start Grading
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
