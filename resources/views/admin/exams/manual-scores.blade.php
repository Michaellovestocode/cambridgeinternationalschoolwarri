@extends('layouts.app')

@section('title', 'Manual Scores - ' . $exam->title)

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-green-700">{{ $exam->subjectModel?->name ?? $exam->subject }}</p>
            <h1 class="text-3xl font-black text-gray-900">{{ $exam->title }}</h1>
            <p class="text-gray-600 mt-1">
                Manual exam mark sheet · {{ $activeSession->name }} · {{ $activeTerm->name }}
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.exams') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-3 rounded-lg font-semibold">
                Back to Exams
            </a>
            <a href="{{ route('admin.exam.edit', $exam->id) }}" class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-800 px-5 py-3 rounded-lg font-semibold">
                Edit Setup
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow p-6">
        <form method="GET" action="{{ route('admin.exam.manual-scores', $exam->id) }}" class="grid md:grid-cols-[1fr_auto] gap-4 items-end">
            <div>
                <label for="class_id" class="block text-sm font-bold text-gray-700 mb-2">Class</label>
                <select id="class_id" name="class_id" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    @foreach($availableClasses as $class)
                        <option value="{{ $class->id }}" {{ $selectedClass->id === $class->id ? 'selected' : '' }}>
                            {{ $class->display_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">Load Class</button>
        </form>
    </div>

    <form method="POST" action="{{ route('admin.exam.manual-scores.store', $exam->id) }}" class="bg-white rounded-2xl shadow p-6 space-y-5">
        @csrf
        <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-gray-900">{{ $selectedClass->display_name }}</h2>
                <p class="text-sm text-gray-600">Enter 1st Test, Notes, and exam marks for all students, then submit once.</p>
            </div>
            <div class="rounded-xl bg-green-50 px-4 py-3 text-sm font-bold text-green-800">
                Limits: 30 + 10 + 60 = 100
            </div>
        </div>

        @if($students->isEmpty())
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg p-6 text-center">
                No students found in this class.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700">
                            <th class="px-4 py-3 text-left">#</th>
                            <th class="px-4 py-3 text-left">Student</th>
                            <th class="px-4 py-3 text-center">1st Test</th>
                            <th class="px-4 py-3 text-center">Notes</th>
                            <th class="px-4 py-3 text-center">Exam /60</th>
                            <th class="px-4 py-3 text-center">Total /100</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $index => $student)
                            @php
                                $score = $scores[$student->id] ?? null;
                                $firstTest = (float) ($score?->ca1 ?? 0);
                                $secondTest = (float) ($score?->ca2 ?? 0);
                                $reportExam = (float) ($score?->exam ?? 0);
                                $total = $firstTest + $secondTest + $reportExam;
                            @endphp
                            <tr class="border-t border-gray-200 score-row">
                                <td class="px-4 py-3 text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-4 py-3">
                                    <input type="hidden" name="scores[{{ $index }}][student_id]" value="{{ $student->id }}">
                                    <p class="font-bold text-gray-900">{{ $student->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $student->registration_number }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number"
                                           name="scores[{{ $index }}][ca1]"
                                           value="{{ old("scores.$index.ca1", $firstTest ?: '') }}"
                                           min="0"
                                           max="30"
                                           step="0.5"
                                           data-first-test
                                           data-score-limit="30"
                                           data-score-label="1st Test"
                                           class="w-24 border border-gray-300 rounded-lg px-3 py-2 text-center focus:border-green-500 focus:ring-green-500">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number"
                                           name="scores[{{ $index }}][ca2]"
                                           value="{{ old("scores.$index.ca2", $secondTest ?: '') }}"
                                           min="0"
                                           max="10"
                                           step="0.5"
                                           data-second-test
                                           data-score-limit="10"
                                           data-score-label="Notes"
                                           class="w-24 border border-gray-300 rounded-lg px-3 py-2 text-center focus:border-green-500 focus:ring-green-500">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number"
                                           name="scores[{{ $index }}][exam]"
                                           value="{{ old("scores.$index.exam", $reportExam ?: '') }}"
                                           min="0"
                                           max="60"
                                           step="0.5"
                                           data-raw-score
                                           data-score-limit="60"
                                           data-score-label="Exam"
                                           class="w-28 border border-gray-300 rounded-lg px-3 py-2 text-center focus:border-green-500 focus:ring-green-500">
                                </td>
                                <td class="px-4 py-3 text-center font-black text-gray-900">
                                    <span data-total>{{ number_format($total, 1) }}</span>
                                    <p class="mt-1 hidden text-xs font-semibold text-red-600" data-score-error></p>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-t pt-5">
                <p class="text-sm text-gray-600">This updates test and exam scores, then refreshes each student's report card.</p>
                <button class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-bold">
                    Save All Scores
                </button>
            </div>
        @endif
    </form>
</div>

<script>
document.querySelectorAll('.score-row').forEach((row) => {
    const inputs = row.querySelectorAll('input[type="number"]');

    function syncTotals() {
        let hasError = false;
        const error = row.querySelector('[data-score-error]');
        const raw = parseFloat(row.querySelector('[data-raw-score]').value) || 0;
        const firstTest = parseFloat(row.querySelector('[data-first-test]').value) || 0;
        const secondTest = parseFloat(row.querySelector('[data-second-test]').value) || 0;

        inputs.forEach((field) => {
            const value = parseFloat(field.value);
            const limit = parseFloat(field.dataset.scoreLimit);
            const isTooHigh = !Number.isNaN(value) && value > limit;
            field.classList.toggle('border-red-500', isTooHigh);
            field.classList.toggle('bg-red-50', isTooHigh);

            if (isTooHigh) {
                hasError = true;
                error.textContent = `${field.dataset.scoreLabel} cannot be more than ${limit}.`;
            }
        });

        error.classList.toggle('hidden', !hasError);
        row.querySelector('[data-total]').textContent = (firstTest + secondTest + raw).toFixed(1);
    }

    inputs.forEach((input) => {
        input.addEventListener('input', syncTotals);
    });
});
</script>
@endsection
