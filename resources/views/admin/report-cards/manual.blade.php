@extends('layouts.app')

@section('title', 'Manual Report Card Builder')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manual Report Card Builder</h1>
            <p class="text-gray-600 mt-1">
                Enter paper-exam scores directly. The normal report card PDF layout remains unchanged.
            </p>
        </div>
        <a href="{{ route('admin.report-cards') }}"
           class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg font-medium">
            Back to Report Cards
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Choose Report Card</h2>
        <form method="GET" action="{{ route('admin.report-cards.manual') }}" class="grid lg:grid-cols-4 gap-4">
            <div>
                <label for="session_id" class="block text-sm font-medium text-gray-700 mb-2">Session</label>
                <select id="session_id" name="session_id" class="w-full border border-gray-300 rounded-lg px-4 py-3" required>
                    @foreach ($sessions as $session)
                        <option value="{{ $session->id }}" {{ (string) request('session_id', $selectedSession?->id) === (string) $session->id ? 'selected' : '' }}>
                            {{ $session->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="term_id" class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                <select id="term_id" name="term_id" class="w-full border border-gray-300 rounded-lg px-4 py-3" required>
                    @foreach ($terms as $term)
                        <option value="{{ $term->id }}" {{ (string) request('term_id', $selectedTerm?->id) === (string) $term->id ? 'selected' : '' }}>
                            {{ $term->name }}{{ $term->session ? ' - ' . $term->session->name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="class_id" class="block text-sm font-medium text-gray-700 mb-2">Class</label>
                <select id="class_id" name="class_id" class="w-full border border-gray-300 rounded-lg px-4 py-3" required>
                    <option value="">Select class</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" {{ (string) $selectedClass?->id === (string) $class->id ? 'selected' : '' }}>
                            {{ $class->display_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                <select id="student_id" name="student_id" class="w-full border border-gray-300 rounded-lg px-4 py-3" required>
                    <option value="">Select student</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" data-class-id="{{ $student->class_id }}" {{ (string) request('student_id') === (string) $student->id ? 'selected' : '' }}>
                            {{ $student->name }}{{ $student->registration_number ? ' - ' . $student->registration_number : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-4 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-lg font-medium">
                    Load Subjects
                </button>
            </div>
        </form>
    </div>

    @if ($selectedClass && $selectedStudent && $selectedSession && $selectedTerm)
        <form method="POST" action="{{ route('admin.report-cards.manual.store') }}" class="bg-white rounded-xl shadow p-6 space-y-6">
            @csrf
            <input type="hidden" name="session_id" value="{{ $selectedSession->id }}">
            <input type="hidden" name="term_id" value="{{ $selectedTerm->id }}">
            <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
            <input type="hidden" name="student_id" value="{{ $selectedStudent->id }}">

            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $selectedStudent->name }}</h2>
                    <p class="text-sm text-gray-600">
                        {{ $selectedClass->display_name }} | {{ $selectedSession->name }} | {{ $selectedTerm->name }}
                    </p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                    <div class="bg-blue-50 text-blue-800 rounded-lg px-4 py-3">
                        <span class="font-semibold">1st Test</span> 0-30
                    </div>
                    <div class="bg-blue-50 text-blue-800 rounded-lg px-4 py-3">
                        <span class="font-semibold">Notes</span> 0-10
                    </div>
                    <div class="bg-blue-50 text-blue-800 rounded-lg px-4 py-3">
                        <span class="font-semibold">Exam</span> 0-60
                    </div>
                </div>
            </div>

            @if ($subjects->isEmpty())
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-6 rounded-lg text-center">
                    No active subjects found. Add subjects before building a manual report card.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700">
                                <th class="px-4 py-3 text-left">Subject</th>
                                <th class="px-4 py-3 text-center">1st Test</th>
                                <th class="px-4 py-3 text-center">Notes</th>
                                <th class="px-4 py-3 text-center">Exam</th>
                                <th class="px-4 py-3 text-center">Total</th>
                                <th class="px-4 py-3 text-center">Current Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subjects as $index => $subject)
                                @php
                                    $score = $scores[$subject->id] ?? null;
                                    $ca1 = old("scores.$index.ca1", $score?->ca1);
                                    $ca2 = old("scores.$index.ca2", $score?->ca2);
                                    $exam = old("scores.$index.exam", $score?->exam);
                                    $total = (float) ($ca1 ?? 0) + (float) ($ca2 ?? 0) + (float) ($exam ?? 0);
                                @endphp
                                <tr class="border-t border-gray-200 score-row">
                                    <td class="px-4 py-3 font-medium text-gray-900">
                                        {{ $subject->name }}
                                        <input type="hidden" name="scores[{{ $index }}][subject_id]" value="{{ $subject->id }}">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="scores[{{ $index }}][ca1]" value="{{ $ca1 }}"
                                               min="0" max="30" step="0.5"
                                               class="score-input w-24 border border-gray-300 rounded-lg px-3 py-2 text-center">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="scores[{{ $index }}][ca2]" value="{{ $ca2 }}"
                                               min="0" max="10" step="0.5"
                                               class="score-input w-24 border border-gray-300 rounded-lg px-3 py-2 text-center">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="scores[{{ $index }}][exam]" value="{{ $exam }}"
                                               min="0" max="60" step="0.5"
                                               class="score-input w-24 border border-gray-300 rounded-lg px-3 py-2 text-center">
                                    </td>
                                    <td class="px-4 py-3 text-center font-semibold text-gray-900">
                                        <span class="row-total">{{ number_format($total, 1) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-700">
                                        {{ $score?->grade ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-600">
                        Saving here creates normal score records, then opens the regular report-card preview for attendance and remarks.
                    </p>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium">
                        Save Scores & Generate Report Card
                    </button>
                </div>
            @endif
        </form>
    @endif
</div>

<script>
document.querySelectorAll('.score-row').forEach((row) => {
    const inputs = row.querySelectorAll('.score-input');
    const total = row.querySelector('.row-total');

    inputs.forEach((input) => {
        input.addEventListener('input', () => {
            const sum = Array.from(inputs).reduce((value, field) => value + (parseFloat(field.value) || 0), 0);
            total.textContent = sum.toFixed(1);
        });
    });
});

const studentSelect = document.getElementById('student_id');
const classSelect = document.getElementById('class_id');

studentSelect?.addEventListener('change', () => {
    const classId = studentSelect.selectedOptions[0]?.dataset.classId;

    if (classId && classSelect) {
        classSelect.value = classId;
    }
});
</script>
@endsection
