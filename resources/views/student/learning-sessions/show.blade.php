@extends('layouts.app')

@section('title', $learningSession->title)

@section('content')
<form action="{{ route('student.learning.submit', $learningSession) }}" method="POST" class="space-y-6">
    @csrf

    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-cyan-600 to-emerald-600 text-white p-8">
            <div class="flex flex-wrap justify-between items-start gap-4">
                <div>
                    <p class="text-cyan-50 font-semibold">{{ $learningSession->subject->name ?? 'Subject' }}</p>
                    <h1 class="text-3xl font-bold mt-2">{{ $learningSession->title }}</h1>
                    <p class="text-cyan-50 mt-2">{{ $learningSession->schoolClass->display_name ?? 'Your class' }} • {{ $learningSession->topic }} • {{ $learningSession->estimated_minutes }} mins</p>
                </div>
                <a href="{{ route('student.learning.index') }}" class="bg-white/15 hover:bg-white/25 px-4 py-2 rounded-lg font-semibold">Back</a>
            </div>
        </div>

        <div class="p-6 md:p-8 space-y-6">
            @if($learningSession->description)
                <p class="text-gray-700 leading-7">{{ $learningSession->description }}</p>
            @endif

            @if($learningSession->learning_goals)
            <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-5">
                <h2 class="font-bold text-emerald-900 mb-3">Learning Goals</h2>
                <ul class="list-disc list-inside space-y-1 text-emerald-900">
                    @foreach(preg_split('/\r\n|\r|\n/', $learningSession->learning_goals) as $goal)
                        @if(trim($goal) !== '')
                            <li>{{ trim($goal) }}</li>
                        @endif
                    @endforeach
                </ul>
            </div>
            @endif

            <div>
                <h2 class="text-xl font-bold text-gray-900 mb-3">Lesson</h2>
                <div class="prose max-w-none text-gray-700 leading-8 whitespace-pre-line">{{ $learningSession->lesson_content ?: 'No lesson content has been added yet.' }}</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
        <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Practice Questions</h2>
                <p class="text-gray-600 text-sm">Choose the best answer for each question.</p>
            </div>
            <span class="bg-cyan-100 text-cyan-800 px-4 py-2 rounded-full text-sm font-bold">{{ $learningSession->questions->count() }} questions</span>
        </div>

        <div class="space-y-6">
            @forelse($learningSession->questions as $question)
            <div class="border border-gray-200 rounded-xl p-5">
                <p class="font-bold text-gray-900 mb-4">{{ $loop->iteration }}. {{ $question->question_text }}</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($question->options as $key => $option)
                    <label class="flex items-start gap-3 border rounded-lg px-4 py-3 cursor-pointer hover:bg-cyan-50">
                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $key }}" class="mt-1">
                        <span><strong>{{ $key }}.</strong> {{ $option }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="text-center py-10 text-gray-500">No questions have been added to this session yet.</div>
            @endforelse
        </div>

        @if($learningSession->questions->count() > 0)
        <div class="mt-8 flex justify-end">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg">
                Submit Practice
            </button>
        </div>
        @endif
    </div>
</form>
@endsection
