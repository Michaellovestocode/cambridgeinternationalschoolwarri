@extends('layouts.app')

@section('title', 'Enter Scores - ' . ($class->name ?? 'Class'))

@section('content')
@php
    $modeLabels = [
        'first_test' => 'CA1',
        'notes' => 'CA2',
        'exam' => 'Exam',
        'all' => 'All Scores',
    ];

    $scoreMode = $scoreMode ?? 'all';
    $scoreFields = $scoreFields ?? ['ca1', 'ca2', 'exam'];
    $modeLabel = $modeLabels[$scoreMode] ?? 'All Scores';
    $sourceBadge = function ($score, string $field) {
        if (!$score) {
            return null;
        }

        $source = $score->{$field . '_source'} ?? null;

        return match ($source) {
            'cbt' => [
                'label' => 'CBT',
                'class' => 'bg-blue-100 text-blue-700 border-blue-200',
                'title' => 'This score came from CBT. If you edit it, the original CBT mark will be kept for audit.',
            ],
            'cbt_overridden' => [
                'label' => 'CBT override',
                'class' => 'bg-orange-100 text-orange-700 border-orange-200',
                'title' => 'This CBT score was manually adjusted and the original CBT mark is still stored.',
            ],
            'paper', 'manual' => [
                'label' => 'Paper',
                'class' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                'title' => 'This score was entered manually.',
            ],
            default => null,
        };
    };
@endphp
<div class="max-w-6xl mx-auto px-3 py-5 sm:px-4 sm:py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-2 sm:text-3xl">
            📝 Enter Scores - {{ $class->name ?? 'Class' }}
        </h1>
        <p class="text-gray-600">{{ $subject->name ?? 'Subject' }} | {{ $activeSession->name ?? '' }} - {{ $activeTerm->name ?? '' }}</p>
        <p class="text-sm font-semibold text-blue-700 mt-2">Current entry mode: {{ $modeLabel }}</p>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-lg border border-red-300 bg-red-50 p-4 text-red-800">
            <p class="font-bold">Please correct the score errors below.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Navigation -->
    <div class="mb-6 flex gap-3">
        <a href="{{ route('teacher.scores.dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-semibold">
            ← Back to Dashboard
        </a>
    </div>

    <div class="mb-6 grid grid-cols-2 md:grid-cols-4 gap-3">
        @foreach($modeLabels as $value => $label)
            <a href="{{ route('teacher.scores.enter') }}?class_id={{ $class->id }}&subject_id={{ $subject->id }}&score_mode={{ $value }}&score_source={{ request('score_source', 'manual') }}"
               class="rounded-lg border-2 px-4 py-3 text-center font-bold transition {{ $scoreMode === $value ? 'border-blue-600 bg-blue-600 text-white shadow' : 'border-gray-200 bg-white text-gray-700 hover:border-blue-300' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <!-- Score Entry Form -->
    <div class="bg-white rounded-lg shadow-lg p-4 sm:p-8">
        <form id="scoresForm" method="POST" class="space-y-6">
            @csrf

            <input type="hidden" name="class_id" value="{{ $class->id }}">
            <input type="hidden" name="subject_id" value="{{ $subject->id }}">
            <input type="hidden" name="score_mode" value="{{ $scoreMode }}">
            <input type="hidden" name="score_source" value="{{ request('score_source', 'manual') }}">

            <!-- Score Grading System Info -->
            <div class="bg-blue-50 border border-blue-300 rounded-lg p-4 mb-6">
                <h3 class="font-bold text-blue-800 mb-2">📊 Grading System</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-blue-800 text-sm">
                    <div>
                        <span class="font-bold">CA1:</span> 0-30
                    </div>
                    <div>
                        <span class="font-bold">CA2:</span> 0-10
                    </div>
                    <div>
                        <span class="font-bold">Exam:</span> 0-60
                    </div>
                </div>
                <p class="text-blue-800 text-sm mt-3">Total = CA1 (30) + CA2 (10) + Exam (60) = 100 marks</p>
                <p class="text-blue-800 text-sm mt-2">CBT scores appear already filled. If you edit a CBT score, the report card uses your new value and the original CBT mark is kept for audit.</p>
            </div>

            <!-- Students Score Table -->
            @if($students->isEmpty())
                <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-6 text-center">
                    <p class="text-yellow-800 font-semibold">No students found in this class</p>
                </div>
            @else
                <div class="overflow-x-auto mb-6">
                    <table class="w-full min-w-[760px] border-collapse text-sm sm:text-base">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="border border-gray-300 px-3 py-3 text-left font-bold sm:px-4">S/N</th>
                                <th class="border border-gray-300 px-3 py-3 text-left font-bold sm:px-4">Student Name</th>
                                @if(in_array('ca1', $scoreFields, true))
                                    <th class="border border-gray-300 px-3 py-3 text-center font-bold sm:px-4">CA1 (30)</th>
                                @endif
                                @if(in_array('ca2', $scoreFields, true))
                                    <th class="border border-gray-300 px-3 py-3 text-center font-bold sm:px-4">CA2 (10)</th>
                                @endif
                                @if(in_array('exam', $scoreFields, true))
                                    <th class="border border-gray-300 px-3 py-3 text-center font-bold sm:px-4">Exam (60)</th>
                                @endif
                                <th class="border border-gray-300 px-3 py-3 text-center font-bold sm:px-4">Total (100)</th>
                                <th class="border border-gray-300 px-3 py-3 text-center font-bold sm:px-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                                @php
                                    $existingScore = $scores[$student->id] ?? null;
                                    $ca1 = (float)($existingScore->ca1 ?? 0);
                                    $ca2 = (float)($existingScore->ca2 ?? 0);
                                    $exam = (float)($existingScore->exam ?? 0);
                                    $ca1Badge = $sourceBadge($existingScore, 'ca1');
                                    $ca2Badge = $sourceBadge($existingScore, 'ca2');
                                    $examBadge = $sourceBadge($existingScore, 'exam');
                                @endphp
                                <tr class="hover:bg-gray-50 border-b border-gray-300">
                                    <td class="border border-gray-300 px-3 py-3 text-center font-bold text-gray-700 sm:px-4">{{ $index + 1 }}</td>
                                    <td class="border border-gray-300 px-3 py-3 font-semibold text-gray-800 sm:px-4">
                                        {{ $student->name }}
                                        <br>
                                        <span class="text-sm text-gray-500">{{ $student->registration_number }}</span>
                                    </td>
                                    <input type="hidden" name="scores[{{ $index }}][student_id]" value="{{ $student->id }}">

                                    @if(in_array('ca1', $scoreFields, true))
                                        <td class="border border-gray-300 px-3 py-3 sm:px-4">
                                            @if($ca1Badge)
                                                <div class="mb-2 text-center">
                                                    <span title="{{ $ca1Badge['title'] }}" class="inline-flex rounded-full border px-2 py-0.5 text-xs font-bold {{ $ca1Badge['class'] }}">
                                                        {{ $ca1Badge['label'] }}
                                                    </span>
                                                </div>
                                            @endif
                                            <input type="number" 
                                                name="scores[{{ $index }}][ca1]" 
                                                value="{{ $ca1 }}"
                                                min="0" max="30" step="0.5"
                                                data-score-limit="30"
                                                data-score-label="CA1"
                                                class="w-full min-w-20 border border-gray-400 rounded px-2 py-2 text-center focus:outline-none focus:border-blue-500"
                                                placeholder="0">
                                        </td>
                                    @else
                                        <input type="hidden" name="scores[{{ $index }}][ca1]" value="{{ $ca1 }}">
                                    @endif

                                    @if(in_array('ca2', $scoreFields, true))
                                        <td class="border border-gray-300 px-3 py-3 sm:px-4">
                                            @if($ca2Badge)
                                                <div class="mb-2 text-center">
                                                    <span title="{{ $ca2Badge['title'] }}" class="inline-flex rounded-full border px-2 py-0.5 text-xs font-bold {{ $ca2Badge['class'] }}">
                                                        {{ $ca2Badge['label'] }}
                                                    </span>
                                                </div>
                                            @endif
                                            <input type="number" 
                                                name="scores[{{ $index }}][ca2]" 
                                                value="{{ $ca2 }}"
                                                min="0" max="10" step="0.5"
                                                data-score-limit="10"
                                                data-score-label="CA2"
                                                class="w-full min-w-20 border border-gray-400 rounded px-2 py-2 text-center focus:outline-none focus:border-blue-500"
                                                placeholder="0">
                                        </td>
                                    @else
                                        <input type="hidden" name="scores[{{ $index }}][ca2]" value="{{ $ca2 }}">
                                    @endif

                                    @if(in_array('exam', $scoreFields, true))
                                        <td class="border border-gray-300 px-3 py-3 sm:px-4">
                                            @if($examBadge)
                                                <div class="mb-2 text-center">
                                                    <span title="{{ $examBadge['title'] }}" class="inline-flex rounded-full border px-2 py-0.5 text-xs font-bold {{ $examBadge['class'] }}">
                                                        {{ $examBadge['label'] }}
                                                    </span>
                                                </div>
                                            @endif
                                            <input type="number" 
                                                name="scores[{{ $index }}][exam]" 
                                                value="{{ $exam }}"
                                                min="0" max="60" step="0.5"
                                                data-score-limit="60"
                                                data-score-label="Exam"
                                                class="w-full min-w-20 border border-gray-400 rounded px-2 py-2 text-center focus:outline-none focus:border-blue-500"
                                                placeholder="0">
                                        </td>
                                    @else
                                        <input type="hidden" name="scores[{{ $index }}][exam]" value="{{ $exam }}">
                                    @endif
                                    <td class="border border-gray-300 px-3 py-3 text-center font-bold bg-gray-100 sm:px-4">
                                        <span class="total-score">{{ ($ca1 ?? 0) + ($ca2 ?? 0) + ($exam ?? 0) }}</span>
                                        <p class="mt-1 hidden text-xs font-semibold text-red-600" data-score-error></p>
                                    </td>
                                    <td class="border border-gray-300 px-3 py-3 text-center sm:px-4">
                                        @if($existingScore)
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $existingScore->status === 'submitted' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                                {{ ucfirst($existingScore->status) }}
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-600">Not saved</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="mobile-action-stack flex gap-4 justify-between pt-6 border-t">
                <div>
                    <a href="{{ route('teacher.scores.dashboard') }}" class="bg-gray-400 hover:bg-gray-500 text-white px-8 py-3 rounded-lg font-semibold">
                        ← Cancel
                    </a>
                </div>
                <div class="mobile-action-stack flex gap-4">
                    <button type="submit" formaction="{{ route('teacher.scores.save') }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-8 py-3 rounded-lg font-semibold">
                        💾 Save as Draft
                    </button>
                    <button type="submit" formaction="{{ route('teacher.scores.submit') }}" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold">
                        ✓ Submit for Approval
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tips Section -->
    <div class="mt-8 bg-green-50 rounded-lg p-6 border border-green-300">
        <h3 class="text-lg font-bold text-green-800 mb-3">💡 Useful Tips</h3>
        <ul class="text-green-700 space-y-2">
            <li>✓ Use decimal values (e.g., 7.5) for precision</li>
            <li>✓ Totals are automatically calculated</li>
            <li>✓ Save regularly as draft to avoid losing data</li>
            <li>✓ Only submit when you're completely sure about the scores</li>
        </ul>
    </div>
</div>

<script>
document.querySelectorAll('#scoresForm tr').forEach((row) => {
    const inputs = row.querySelectorAll('input[type="number"]');

    if (!inputs.length) {
        return;
    }

    function syncRow() {
        let hasError = false;
        const error = row.querySelector('[data-score-error]');
        const ca1 = parseFloat(row.querySelector('input[name*="[ca1]"]').value) || 0;
        const ca2 = parseFloat(row.querySelector('input[name*="[ca2]"]').value) || 0;
        const exam = parseFloat(row.querySelector('input[name*="[exam]"]').value) || 0;

        inputs.forEach((field) => {
            const value = parseFloat(field.value);
            const limit = parseFloat(field.dataset.scoreLimit);
            const isTooHigh = !Number.isNaN(value) && value > limit;
            const message = isTooHigh ? `${field.dataset.scoreLabel} cannot be more than ${limit}.` : '';

            field.setCustomValidity(message);
            field.classList.toggle('border-red-500', isTooHigh);
            field.classList.toggle('bg-red-50', isTooHigh);

            if (isTooHigh && error) {
                hasError = true;
                error.textContent = message;
            }
        });

        if (error) {
            error.classList.toggle('hidden', !hasError);
        }

        row.querySelector('.total-score').textContent = (ca1 + ca2 + exam).toFixed(1);
    }

    inputs.forEach((input) => {
        input.addEventListener('input', syncRow);
        input.addEventListener('change', syncRow);
    });

    syncRow();
});
</script>

@endsection
