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
            <form action="{{ route('admin.learning-sessions.questions.store', $learningSession) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Question Type</label>
                    <select name="question_type" id="question_type" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                        <option value="objective" @selected(old('question_type', $learningSession->assessment_format === 'theory' ? 'theory' : 'objective') === 'objective')>Objective / MCQ</option>
                        <option value="theory" @selected(old('question_type', $learningSession->assessment_format === 'theory' ? 'theory' : 'objective') === 'theory')>Theory / Written</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Question</label>
                    <textarea name="question_text" rows="4" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">{{ old('question_text') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Marks *</label>
                    <input type="number" name="marks" value="{{ old('marks', 1) }}" min="0.01" step="0.01" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reference Image <span class="text-gray-500">(Optional)</span></label>
                    <div x-data="{ preview: null }" class="space-y-2">
                        <input type="file" name="image" accept="image/*" @change="
                            const file = $event.target.files[0];
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = (e) => { preview = e.target.result };
                                reader.readAsDataURL(file);
                            }
                        " class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-cyan-500">
                        <p class="text-xs text-gray-500">Upload a reference image if this question depends on a diagram, chart, or picture.</p>
                        <div x-show="preview" class="mt-3 border rounded-lg overflow-hidden">
                            <div class="bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 flex justify-between items-center">
                                <span>Preview</span>
                                <button type="button" @click="preview = null; $event.target.closest('div').querySelector('input[type=file]').value = ''" class="text-red-500 hover:text-red-700">Remove</button>
                            </div>
                            <img :src="preview" alt="Preview" class="max-h-48 w-full object-contain bg-white p-2">
                        </div>
                    </div>
                </div>

                <div id="objective-options" class="space-y-4 @if(old('question_type', $learningSession->assessment_format === 'theory' ? 'theory' : 'objective') === 'theory') hidden @endif">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach(['A', 'B', 'C', 'D'] as $letter)
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Option {{ $letter }}</label>
                                <input type="text" name="options[{{ $letter }}]" value="{{ old('options.' . $letter) }}" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500" placeholder="Option {{ $letter }}">
                            </div>
                        @endforeach
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Correct Answer</label>
                        <select name="correct_option" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                            <option value="">-- Select Correct Answer --</option>
                            @foreach(['A', 'B', 'C', 'D'] as $option)
                                <option value="{{ $option }}" @selected(old('correct_option') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="theory-options" class="@if(old('question_type', $learningSession->assessment_format === 'theory' ? 'theory' : 'objective') !== 'theory') hidden @endif">
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                        Theory questions do not need multiple-choice options. Students will answer in written form.
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Order</label>
                        <input type="number" min="0" name="order" value="{{ old('order', $learningSession->questions->count() + 1) }}" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Explanation <span class="text-gray-500">(Optional)</span></label>
                        <textarea name="explanation" rows="3" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">{{ old('explanation') }}</textarea>
                    </div>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const questionType = document.getElementById('question_type');
        const objectiveOptions = document.getElementById('objective-options');
        const theoryOptions = document.getElementById('theory-options');
        const previewObjective = document.getElementById('preview-objective');
        const previewTheory = document.getElementById('preview-theory');

        if (questionType) {
            const syncQuestionType = () => {
                const isTheory = questionType.value === 'theory';
                objectiveOptions.classList.toggle('hidden', isTheory);
                theoryOptions.classList.toggle('hidden', !isTheory);

                if (previewObjective) previewObjective.classList.toggle('hidden', isTheory);
                if (previewTheory) previewTheory.classList.toggle('hidden', !isTheory);
            };

            questionType.addEventListener('change', syncQuestionType);
            syncQuestionType();
        }
    });
</script>
@endsection
