@extends('layouts.app')

@section('title', 'Edit Learning Session')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Learning Session</h1>
                <p class="text-gray-600 text-sm">{{ $learningSession->schoolClass->display_name ?? 'No class' }} • {{ $learningSession->subject->name ?? 'N/A' }} • {{ $learningSession->topic }}</p>
            </div>
            <a href="{{ route('admin.learning-sessions.index') }}" class="text-blue-600 hover:underline">Back to sessions</a>
        </div>

        @include('admin.learning-sessions.partials.form', [
            'action' => route('admin.learning-sessions.update', $learningSession),
            'method' => 'PUT',
            'learningSession' => $learningSession,
            'subjects' => $subjects,
            'classes' => $classes,
        ])
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Add Practice Question</h2>
            <form action="{{ route('admin.learning-sessions.questions.store', $learningSession) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Question</label>
                    <textarea name="question_text" rows="4" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">{{ old('question_text') }}</textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach(['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'] as $key => $label)
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Option {{ $label }}{{ in_array($label, ['A', 'B']) ? '' : ' (optional)' }}</label>
                        <input type="text" name="option_{{ $key }}" value="{{ old('option_' . $key) }}" {{ in_array($label, ['A', 'B']) ? 'required' : '' }} class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                    </div>
                    @endforeach
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Correct Option</label>
                        <select name="correct_option" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                            @foreach(['A', 'B', 'C', 'D'] as $option)
                                <option value="{{ $option }}" @selected(old('correct_option') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Order</label>
                        <input type="number" min="0" name="order" value="{{ old('order', $learningSession->questions->count() + 1) }}" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Explanation</label>
                    <textarea name="explanation" rows="3" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">{{ old('explanation') }}</textarea>
                </div>
                <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 text-white px-6 py-3 rounded-lg font-bold">Add Question</button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Question Bank</h2>
            <div class="space-y-4">
                @forelse($learningSession->questions as $question)
                <div class="border rounded-lg p-4">
                    <div class="flex justify-between gap-4">
                        <p class="font-semibold text-gray-900">{{ $loop->iteration }}. {{ $question->question_text }}</p>
                        <form action="{{ route('admin.learning-sessions.questions.destroy', $question) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline text-sm" onclick="return confirm('Delete this question?')">Delete</button>
                        </form>
                    </div>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                        @foreach($question->options as $key => $option)
                            <div class="{{ $key === $question->correct_option ? 'bg-green-50 text-green-800' : 'bg-gray-50 text-gray-700' }} rounded px-3 py-2">
                                <strong>{{ $key }}.</strong> {{ $option }}
                            </div>
                        @endforeach
                    </div>
                    @if($question->explanation)
                        <p class="mt-3 text-sm text-gray-600"><strong>Explanation:</strong> {{ $question->explanation }}</p>
                    @endif
                </div>
                @empty
                <p class="text-gray-500 text-center py-10">No questions added yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
