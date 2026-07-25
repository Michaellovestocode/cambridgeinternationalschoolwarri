@extends('layouts.app')

@section('title', 'Select Class and Subject')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">📋 Select Class and Subject</h1>
        <p class="text-gray-600">Choose the class, subject, and report-card score component you want to enter now.</p>
    </div>

    <!-- Session/Term Info -->
    @if($activeSession && $activeTerm)
    <div class="bg-blue-50 border border-blue-300 rounded-lg p-4 mb-6">
        <p class="text-blue-800">
            <strong>Current Session:</strong> {{ $activeSession->name ?? 'N/A' }} | 
            <strong>Term:</strong> {{ $activeTerm->name ?? 'N/A' }}
        </p>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow-lg p-8">
        <form action="{{ route('teacher.scores.enter') }}" method="GET" class="space-y-6">
            <input type="hidden" name="score_source" value="{{ request('score_source', 'manual') }}">

            <!-- Class Selection -->
            <div>
                <label for="class_id" class="block text-gray-700 font-bold mb-2">
                    <span class="text-red-500">*</span> Select Class
                </label>
                <select name="class_id" id="class_id" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500 @error('class_id') border-red-500 @enderror" required>
                    <option value="">-- Choose a class --</option>
                    @forelse($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }} - {{ $class->description ?? 'No description' }}
                        </option>
                    @empty
                        <option value="" disabled>No classes available</option>
                    @endforelse
                </select>
                @error('class_id')
                    <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Subject Selection -->
            <div>
                <label for="subject_id" class="block text-gray-700 font-bold mb-2">
                    <span class="text-red-500">*</span> Select Subject
                </label>
                <select name="subject_id" id="subject_id" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500 @error('subject_id') border-red-500 @enderror" required>
                    <option value="">-- Choose a subject --</option>
                    @forelse($subjects as $subject)
                        <option value="{{ $subject->id }}" data-subject-option {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }} ({{ $subject->code ?? 'N/A' }})
                        </option>
                    @empty
                        <option value="" disabled>No subjects available</option>
                    @endforelse
                </select>
                <p id="subject_empty_message" class="hidden text-sm text-amber-700 mt-2">
                    No subject is assigned to you for this class.
                </p>
                @error('subject_id')
                    <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Optional Student Selection -->
            <div>
                <label for="student_id" class="block text-gray-700 font-bold mb-2">
                    Select Student (optional)
                </label>
                <select name="student_id" id="student_id" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500">
                    <option value="">-- All students in class (default) --</option>
                </select>
                <p id="student_empty_message" class="hidden text-sm text-amber-700 mt-2">
                    No students found for this class.
                </p>
            </div>

            <!-- Score Mode -->
            <div>
                <label class="block text-gray-700 font-bold mb-3">
                    <span class="text-red-500">*</span> What score do you want to enter?
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    @php
                        $modes = [
                            'first_test' => ['title' => 'CA1', 'detail' => 'Test score, out of 30'],
                            'notes' => ['title' => 'CA2', 'detail' => 'Note score, out of 10'],
                            'exam' => ['title' => 'Exam', 'detail' => 'Out of 60'],
                            'all' => ['title' => 'All Scores', 'detail' => '30 + 10 + 60'],
                        ];
                    @endphp
                    @foreach($modes as $value => $mode)
                        <label class="cursor-pointer">
                            <input type="radio" name="score_mode" value="{{ $value }}" class="peer sr-only" {{ old('score_mode', 'first_test') === $value ? 'checked' : '' }}>
                            <span class="block rounded-lg border-2 border-gray-200 bg-white p-4 transition peer-checked:border-blue-600 peer-checked:bg-blue-50">
                                <span class="block font-bold text-gray-900">{{ $mode['title'] }}</span>
                                <span class="block text-sm text-gray-600">{{ $mode['detail'] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-300 rounded-lg p-4">
                <p class="text-blue-800">
                    <strong>Note:</strong> Use Save Draft while the term is ongoing. Final Submit refreshes the report cards and sends them back for form-teacher review.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4 pt-6">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
                    ➜ Continue to Score Entry
                </button>
                <a href="{{ route('teacher.scores.dashboard') }}" class="flex-1 text-center bg-gray-400 hover:bg-gray-500 text-white font-bold py-3 px-6 rounded-lg transition">
                    ← Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Help Section -->
    <div class="mt-8 bg-gray-50 rounded-lg p-6 border border-gray-300">
        <h3 class="text-lg font-bold text-gray-800 mb-3">❓ Need Help?</h3>
        <ul class="text-gray-700 space-y-2">
            <li>• Make sure you select both a class and a subject</li>
            <li>• You can only enter scores for classes and subjects you're assigned to</li>
            <li>• Contact the admin if you need access to additional classes</li>
        </ul>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const subjectsByClass = @json($subjectsByClass ?? []);
    const studentsByClass = @json($studentsByClass ?? []);
    const classSelect = document.getElementById('class_id');
    const subjectSelect = document.getElementById('subject_id');
    const studentSelect = document.getElementById('student_id');
    const subjectOptions = Array.from(subjectSelect.querySelectorAll('[data-subject-option]'));
    const emptyMessage = document.getElementById('subject_empty_message');
    const studentEmptyMessage = document.getElementById('student_empty_message');

    function syncSubjectOptions() {
        const classId = classSelect.value;
        const allowedSubjectIds = new Set((subjectsByClass[classId] || []).map(function (id) {
            return String(id);
        }));
        let visibleCount = 0;

        subjectOptions.forEach(function (option) {
            const isVisible = classId !== '' && allowedSubjectIds.has(String(option.value));

            option.hidden = !isVisible;
            option.disabled = !isVisible;

            if (isVisible) {
                visibleCount++;
            }
        });

        if (subjectSelect.value && !allowedSubjectIds.has(String(subjectSelect.value))) {
            subjectSelect.value = '';
        }

        subjectSelect.disabled = classId === '' || visibleCount === 0;
        emptyMessage.classList.toggle('hidden', classId === '' || visibleCount > 0);
    }

    function syncStudentOptions() {
        const classId = classSelect.value;
        const students = (studentsByClass[classId] || []);

        // Clear existing options except the default
        studentSelect.querySelectorAll('option:not([value=""])').forEach(opt => opt.remove());

        if (!classId || students.length === 0) {
            studentSelect.disabled = true;
            studentEmptyMessage.classList.toggle('hidden', !(classId && students.length === 0));
            return;
        }

        students.forEach(function (s) {
            const option = document.createElement('option');
            option.value = s.id;
            option.textContent = s.name + ' — ' + (s.registration_number || 'N/A');
            studentSelect.appendChild(option);
        });

        studentSelect.disabled = false;
        studentEmptyMessage.classList.add('hidden');
    }

    classSelect.addEventListener('change', syncSubjectOptions);
    classSelect.addEventListener('change', syncStudentOptions);
    syncSubjectOptions();
    syncStudentOptions();
});
</script>

@endsection
