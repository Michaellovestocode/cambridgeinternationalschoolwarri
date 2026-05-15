@extends('layouts.app')

@section('title', 'Edit Exam')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Edit Exam</h2>
            <a href="{{ route('admin.exams') }}" class="text-gray-600 hover:text-gray-800">
                ← Back to Exams
            </a>
        </div>

        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('admin.exam.update', $exam->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Exam Title *</label>
                <input type="text" name="title" value="{{ old('title', $exam->title) }}" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="e.g., Computer Science Mid-Term Exam" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" rows="3" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                          placeholder="Brief description of the exam">{{ old('description', $exam->description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                    <select name="subject_id" id="subject_id"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           required>
                        <option value="">-- Select a Subject --</option>
                        @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ old('subject_id', $exam->subject_id) == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }} ({{ $subject->code }})
                        </option>
                        @endforeach
                    </select>
                    @error('subject_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Duration (Minutes) *</label>
                    <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes) }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           min="1" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Total Marks *</label>
                    <input type="number" name="total_marks" value="{{ old('total_marks', $exam->total_marks) }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           min="1" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pass Mark *</label>
                    <input type="number" name="pass_mark" value="{{ old('pass_mark', $exam->pass_mark) }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           min="0" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
                    <input type="datetime-local" name="start_date" 
                           value="{{ old('start_date', $exam->start_date->format('Y-m-d\TH:i')) }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date *</label>
                    <input type="datetime-local" name="end_date" 
                           value="{{ old('end_date', $exam->end_date->format('Y-m-d\TH:i')) }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Instructions</label>
                <textarea name="instructions" rows="4" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                          placeholder="Instructions for students (e.g., Answer all questions, No use of calculators)">{{ old('instructions', $exam->instructions) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Assign to Classes *</label>
                <div id="classes_list" class="space-y-2 bg-gray-50 p-4 rounded-lg">
                    @foreach($classes as $class)
                    <label class="flex items-center" data-class-option data-class-id="{{ $class->id }}">
                        <input type="checkbox" name="classes[]" value="{{ $class->id }}" 
                               {{ in_array($class->id, old('classes', $exam->classes->pluck('id')->toArray())) ? 'checked' : '' }}
                               class="mr-2 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <span class="font-medium">{{ $class->display_name }}</span>
                    </label>
                    @endforeach
                </div>
                <p id="no_classes_message" class="hidden text-sm text-gray-600 bg-gray-50 rounded-lg px-4 py-3">
                    Select a subject to see the classes available for it.
                </p>
                @error('classes')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-600 mt-2">
                    ℹ️ You can add or remove classes. Students in newly added classes will be able to take this exam.
                </p>
            </div>

            <div class="border-t pt-4">
                <label class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" 
                           {{ old('is_active', $exam->is_active) ? 'checked' : '' }}
                           class="mr-2 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <span class="font-medium">Exam is Active (visible to students)</span>
                </label>
            </div>

            <div class="border border-gray-200 rounded-xl p-4">
                <label class="block text-sm font-medium text-gray-700 mb-3">Assessment Type *</label>
                <div class="grid gap-3 md:grid-cols-2">
                    <label class="flex items-start rounded-lg border border-gray-200 p-4 cursor-pointer hover:border-green-500">
                        <input type="radio" name="grading_mode" value="auto" class="mt-1 mr-3 text-green-600 focus:ring-green-500"
                               {{ old('grading_mode', $exam->grading_mode ?? 'auto') === 'auto' ? 'checked' : '' }}>
                        <span>
                            <span class="block font-semibold text-gray-900">Auto-gradable CBT</span>
                            <span class="block text-sm text-gray-600">Students take the exam online. Objective questions are marked by the computer.</span>
                        </span>
                    </label>
                    <label class="flex items-start rounded-lg border border-gray-200 p-4 cursor-pointer hover:border-green-500">
                        <input type="radio" name="grading_mode" value="manual" class="mt-1 mr-3 text-green-600 focus:ring-green-500"
                               {{ old('grading_mode', $exam->grading_mode ?? 'auto') === 'manual' ? 'checked' : '' }}>
                        <span>
                            <span class="block font-semibold text-gray-900">Manual mark entry</span>
                            <span class="block text-sm text-gray-600">No questions needed. Enter all students' exam marks in one fast sheet.</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="border-t pt-4">
                <label class="flex items-start">
                    <input type="hidden" name="show_results_to_students" value="0">
                    <input type="checkbox" name="show_results_to_students" value="1"
                           {{ old('show_results_to_students', $exam->show_results_to_students) ? 'checked' : '' }}
                           class="mt-1 mr-2 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <span>
                        <span class="font-medium text-gray-800">Release scores and scripts to students</span>
                        <span class="block text-sm text-gray-600">When off, students only see a submitted-successfully message for this exam.</span>
                    </span>
                </label>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold">
                    Update Exam
                </button>
                <a href="{{ route('admin.exams') }}" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-8 py-3 rounded-lg font-semibold">
                    Cancel
                </a>
            </div>
        </form>

        <div class="mt-8 pt-6 border-t">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Quick Actions</h3>
            <div class="flex gap-3">
                @if(($exam->grading_mode ?? 'auto') === 'manual')
                    <a href="{{ route('admin.exam.manual-scores', $exam->id) }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                        Enter Manual Scores
                    </a>
                @else
                    <a href="{{ route('admin.exam.questions', $exam->id) }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                        Manage Questions ({{ $exam->questions->count() }})
                    </a>
                @endif
                <a href="{{ route('admin.exam.results', $exam->id) }}" 
                   class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">
                    View Results
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const classesBySubject = @json($classesBySubject);
    const subjectSelect = document.getElementById('subject_id');
    const classOptions = Array.from(document.querySelectorAll('[data-class-option]'));
    const noClassesMessage = document.getElementById('no_classes_message');

    function syncClassOptions() {
        const subjectId = subjectSelect.value;
        const allowedClassIds = new Set((classesBySubject[subjectId] || []).map(function (classItem) {
            return String(classItem.id);
        }));
        let visibleCount = 0;

        classOptions.forEach(function (option) {
            const checkbox = option.querySelector('input[type="checkbox"]');
            const isVisible = subjectId !== '' && allowedClassIds.has(String(option.dataset.classId));
            option.classList.toggle('hidden', !isVisible);

            if (!isVisible) {
                checkbox.checked = false;
            } else {
                visibleCount++;
            }
        });

        noClassesMessage.classList.toggle('hidden', subjectId !== '' && visibleCount > 0);
        noClassesMessage.textContent = subjectId === ''
            ? 'Select a subject to see the classes available for it.'
            : 'No class is assigned to you for this subject.';
    }

    subjectSelect.addEventListener('change', syncClassOptions);
    syncClassOptions();
});
</script>
@endsection
