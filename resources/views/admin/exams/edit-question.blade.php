@extends('layouts.app')

@section('title', 'Edit Question')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Edit Question</h2>
                <p class="text-gray-600">{{ $exam->title }} - {{ $exam->subject }}</p>
            </div>
            <a href="{{ route('admin.exam.questions', $exam->id) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                Back to Questions
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6" x-data="{ questionType: '{{ old('question_type', $question->question_type) }}', preview: null }">
        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('admin.question.update', $question->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Attach to Passage <span class="text-gray-500">(Optional)</span></label>
                <select name="question_passage_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="">Standalone question</option>
                    @foreach($exam->passages as $passage)
                    <option value="{{ $passage->id }}" {{ old('question_passage_id', $question->question_passage_id) == $passage->id ? 'selected' : '' }}>
                        {{ $passage->title ?: 'Passage ' . $loop->iteration }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Question Type *</label>
                <select name="question_type" x-model="questionType"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="multiple_choice">Multiple Choice (A/B/C/D)</option>
                    <option value="theory">Theory/Essay</option>
                    <option value="coding">Coding</option>
                    <option value="fill_blank">Fill in the Blank</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Question Text *</label>
                @include('admin.exams.partials.rich-question-editor', ['value' => old('question_text', $question->question_text)])
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Marks *</label>
                <input type="number" name="marks" value="{{ old('marks', $question->marks) }}" min="1" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Reference Image <span class="text-gray-500">(Optional)</span>
                </label>

                @if($question->image_path)
                <div class="mb-3 border rounded-lg overflow-hidden">
                    <div class="bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">Current Image</div>
                    <img src="{{ asset('storage/' . $question->image_path) }}" alt="Current question image" class="max-h-48 w-full object-contain bg-white p-2">
                    <label class="flex items-center gap-2 px-3 py-2 text-sm text-red-700 bg-red-50 border-t">
                        <input type="checkbox" name="remove_image" value="1" class="rounded border-red-300 text-red-600 focus:ring-red-500">
                        Remove current image
                    </label>
                </div>
                @endif

                <input type="file"
                       name="image"
                       accept="image/*"
                       @change="
                           const file = $event.target.files[0];
                           if (file) {
                               const reader = new FileReader();
                               reader.onload = (e) => { preview = e.target.result };
                               reader.readAsDataURL(file);
                           } else {
                               preview = null;
                           }
                       "
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-green-500">
                <p class="text-xs text-gray-500 mt-2">Upload a new image to replace the current one. Max 5MB (JPG, PNG, GIF, WEBP)</p>

                <div x-show="preview" class="mt-3 border rounded-lg overflow-hidden">
                    <div class="bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">New Image Preview</div>
                    <img :src="preview" alt="New image preview" class="max-h-48 w-full object-contain bg-white p-2">
                </div>
            </div>

            <div x-show="questionType === 'multiple_choice'" class="space-y-3">
                <label class="block text-sm font-medium text-gray-700">Options *</label>

                @foreach(['A', 'B', 'C', 'D'] as $letter)
                <div class="flex gap-2">
                    <span class="font-bold text-gray-700 w-8">{{ $letter }}.</span>
                    <input type="text" name="options[{{ $letter }}]" value="{{ old('options.' . $letter, $question->options[$letter] ?? '') }}"
                           :required="questionType === 'multiple_choice'"
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg"
                           placeholder="Option {{ $letter }}">
                </div>
                @endforeach

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Correct Answer *</label>
                    <select name="correct_answer"
                            :required="questionType === 'multiple_choice'"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">-- Select Correct Answer --</option>
                        @foreach(['A', 'B', 'C', 'D'] as $letter)
                        <option value="{{ $letter }}" {{ old('correct_answer', $question->correct_answer) == $letter ? 'selected' : '' }}>{{ $letter }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <template x-if="questionType === 'fill_blank'">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Correct Answer *</label>
                    <input type="text" name="correct_answer" value="{{ old('correct_answer', $question->question_type === 'fill_blank' ? $question->correct_answer : '') }}"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                           placeholder="Enter the correct answer (case-insensitive)">
                </div>
            </template>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
                    Save Changes
                </button>
                <a href="{{ route('admin.exam.questions', $exam->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-lg font-semibold">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
