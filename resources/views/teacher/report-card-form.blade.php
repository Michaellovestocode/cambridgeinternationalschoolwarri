@extends('layouts.app')

@section('title', $student ? 'Edit Report Card - ' . $student->name : 'Create Report Card')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ $student ? 'Edit Report Card' : 'Create Report Card' }}
                    </h1>
                    <p class="text-gray-600 mt-1">
                        {{ $student ? 'Update report card for ' . $student->name : 'Create a new report card for a student in your assigned classes' }}
                    </p>
                </div>
                <a href="{{ route('teacher.scores.dashboard') }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 ease-in-out">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Report Cards
                </a>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('teacher.report-card.store') }}" class="bg-white shadow-sm rounded-lg p-6">
            @csrf

            <!-- Display Validation Errors -->
            @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <i class="fas fa-exclamation-triangle text-red-400 mt-0.5 mr-3"></i>
                    <div>
                        <h3 class="text-sm font-medium text-red-800">There were some errors with your submission:</h3>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <!-- Student Selection (only show if not editing) -->
            @if(!$student)
            <div class="mb-6">
                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Select Student</label>
                <select name="student_id" id="student_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('student_id') ? 'border-red-500' : '' }}">
                    <option value="">Choose a student...</option>
                    @foreach($formTeacherAssignments as $assignment)
                    <optgroup label="{{ $assignment->schoolClass->name }}">
                        @foreach($assignment->schoolClass->students as $classStudent)
                        <option value="{{ $classStudent->id }}" {{ old('student_id') == $classStudent->id ? 'selected' : '' }}>{{ $classStudent->name }} ({{ $classStudent->email }})</option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
                @error('student_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            @else
            <input type="hidden" name="student_id" value="{{ $student->id }}">
            @endif

            <!-- Session and Term -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="session_id" class="block text-sm font-medium text-gray-700 mb-2">Academic Session</label>
                    <select name="session_id" id="session_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('session_id') ? 'border-red-500' : '' }}">
                        <option value="">Select session...</option>
                        @foreach($sessions as $session)
                        <option value="{{ $session->id }}" {{ old('session_id', $reportCard ? $reportCard->session_id : '') == $session->id ? 'selected' : '' }}>
                            {{ $session->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('session_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="term_id" class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                    <select name="term_id" id="term_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('term_id') ? 'border-red-500' : '' }}">
                        <option value="">Select term...</option>
                        @foreach($terms as $term)
                        <option value="{{ $term->id }}" {{ old('term_id', $reportCard ? $reportCard->term_id : '') == $term->id ? 'selected' : '' }}>
                            {{ $term->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('term_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Subjects Section -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Subject Scores</h3>
                <div id="subjects-container">
                    @if($reportCard && $reportCard->subjects)
                        @foreach($reportCard->subjects as $index => $subjectData)
                        <div class="subject-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-4 p-4 bg-gray-50 rounded-lg">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                                <select name="subjects[{{ $index }}][subject_id]" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select subject...</option>
                                    @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ $subjectData['subject_id'] == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Score (%)</label>
                                <input type="number" name="subjects[{{ $index }}][score]" min="0" max="100" step="0.01"
                                       value="{{ $subjectData['score'] }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Grade</label>
                                <input type="text" name="subjects[{{ $index }}][grade]" maxlength="2"
                                       value="{{ $subjectData['grade'] }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Remark</label>
                                <input type="text" name="subjects[{{ $index }}][remark]" maxlength="255"
                                       value="{{ $subjectData['remark'] ?? '' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="flex items-end">
                                <button type="button" onclick="removeSubject(this)"
                                        class="w-full bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg font-medium transition duration-150 ease-in-out">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
                <button type="button" onclick="addSubject()"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 ease-in-out">
                    <i class="fas fa-plus mr-2"></i>Add Subject
                </button>
            </div>

            <!-- Attendance Fields -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Attendance Record</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label for="days_school_opened" class="block text-sm font-medium text-gray-700 mb-2">Days School Opened</label>
                        <input type="number" name="days_school_opened" id="days_school_opened" min="0" required
                               value="{{ $reportCard ? $reportCard->days_school_opened : '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        @error('days_school_opened')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="days_present" class="block text-sm font-medium text-gray-700 mb-2">Days Present</label>
                        <input type="number" name="days_present" id="days_present" min="0" required
                               value="{{ $reportCard ? $reportCard->days_present : '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        @error('days_present')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="days_absent" class="block text-sm font-medium text-gray-700 mb-2">Days Absent</label>
                        <input type="number" name="days_absent" id="days_absent" min="0" required
                               value="{{ $reportCard ? $reportCard->days_absent : '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        @error('days_absent')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="attendance_percentage" class="block text-sm font-medium text-gray-700 mb-2">Attendance %</label>
                        <input type="number" name="attendance_percentage" id="attendance_percentage" min="0" max="100" step="0.01" required
                               value="{{ $reportCard ? $reportCard->attendance_percentage : '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        @error('attendance_percentage')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Class Teacher Comment -->
            <div class="mb-6">
                <label for="class_teacher_comment" class="block text-sm font-medium text-gray-700 mb-2">Class Teacher's Comment</label>
                <textarea name="class_teacher_comment" id="class_teacher_comment" rows="3" maxlength="500"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Enter class teacher's comment...">{{ $reportCard ? $reportCard->class_teacher_comment : '' }}</textarea>
                @error('class_teacher_comment')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <!-- Head Teacher Comment -->
            <div class="mb-6">
                <label for="head_teacher_comment" class="block text-sm font-medium text-gray-700 mb-2">Head Teacher's Comment</label>
                <textarea name="head_teacher_comment" id="head_teacher_comment" rows="3" maxlength="500"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Enter head teacher's comment...">{{ $reportCard ? $reportCard->head_teacher_comment : '' }}</textarea>
                @error('head_teacher_comment')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition duration-150 ease-in-out">
                    <i class="fas fa-save mr-2"></i>{{ $student ? 'Update Report Card' : 'Create Report Card' }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let subjectIndex = {{ $reportCard && $reportCard->subjects ? count($reportCard->subjects) : 0 }};

// Create subjects data for JavaScript
const subjectsData = @json($subjects);

// Get old input data
const oldSubjects = @json(old('subjects', []));

function addSubject(subjectData = null) {
    const container = document.getElementById('subjects-container');
    const subjectRow = document.createElement('div');
    subjectRow.className = 'subject-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-4 p-4 bg-gray-50 rounded-lg';

    let subjectOptions = '<option value="">Select subject...</option>';
    subjectsData.forEach(subject => {
        const selected = subjectData && subjectData.subject_id == subject.id ? 'selected' : '';
        subjectOptions += `<option value="${subject.id}" ${selected}>${subject.name}</option>`;
    });

    const scoreValue = subjectData ? subjectData.score : '';
    const gradeValue = subjectData ? subjectData.grade : '';
    const remarkValue = subjectData ? subjectData.remark || '' : '';

    subjectRow.innerHTML = `
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
            <select name="subjects[${subjectIndex}][subject_id]" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                ${subjectOptions}
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Score (%)</label>
            <input type="number" name="subjects[${subjectIndex}][score]" min="0" max="100" step="0.01" required
                   value="${scoreValue}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Grade</label>
            <input type="text" name="subjects[${subjectIndex}][grade]" maxlength="2" required
                   value="${gradeValue}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Remark</label>
            <input type="text" name="subjects[${subjectIndex}][remark]" maxlength="255"
                   value="${remarkValue}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div class="flex items-end">
            <button type="button" onclick="removeSubject(this)"
                    class="w-full bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg font-medium transition duration-150 ease-in-out">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(subjectRow);
    subjectIndex++;
}

// Load old input subjects on page load
document.addEventListener('DOMContentLoaded', function() {
    if (oldSubjects && oldSubjects.length > 0) {
        oldSubjects.forEach(subject => {
            addSubject(subject);
        });
    }
});

function removeSubject(button) {
    button.closest('.subject-row').remove();
}
</script>

<!-- Success Message -->
@if(session('success'))
<div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg z-50" id="success-message">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        {{ session('success') }}
    </div>
</div>
<script>
    setTimeout(() => {
        document.getElementById('success-message').style.display = 'none';
    }, 5000);
</script>
@endif
@endsection