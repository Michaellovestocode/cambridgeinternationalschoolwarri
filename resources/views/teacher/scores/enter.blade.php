@extends('layouts.app')

@section('title', 'Enter Scores - ' . ($class->name ?? 'Class'))

@section('content')
@php
    $modeLabels = [
        'first_test' => '1st Test',
        'notes' => 'Notes',
        'exam' => 'Exam',
        'all' => 'All Scores',
    ];

    $scoreMode = $scoreMode ?? 'all';
    $scoreFields = $scoreFields ?? ['ca1', 'ca2', 'exam'];
    $modeLabel = $modeLabels[$scoreMode] ?? 'All Scores';
@endphp
<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
            📝 Enter Scores - {{ $class->name ?? 'Class' }}
        </h1>
        <p class="text-gray-600">{{ $subject->name ?? 'Subject' }} | {{ $activeSession->name ?? '' }} - {{ $activeTerm->name ?? '' }}</p>
        <p class="text-sm font-semibold text-blue-700 mt-2">Current entry mode: {{ $modeLabel }}</p>
    </div>

    <!-- Navigation -->
    <div class="mb-6 flex gap-3">
        <a href="{{ route('teacher.scores.dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-semibold">
            ← Back to Dashboard
        </a>
    </div>

    <div class="mb-6 grid grid-cols-2 md:grid-cols-4 gap-3">
        @foreach($modeLabels as $value => $label)
            <a href="{{ route('teacher.scores.enter') }}?class_id={{ $class->id }}&subject_id={{ $subject->id }}&score_mode={{ $value }}"
               class="rounded-lg border-2 px-4 py-3 text-center font-bold transition {{ $scoreMode === $value ? 'border-blue-600 bg-blue-600 text-white shadow' : 'border-gray-200 bg-white text-gray-700 hover:border-blue-300' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <!-- Score Entry Form -->
    <div class="bg-white rounded-lg shadow-lg p-8">
        <form id="scoresForm" method="POST" class="space-y-6">
            @csrf

            <input type="hidden" name="class_id" value="{{ $class->id }}">
            <input type="hidden" name="subject_id" value="{{ $subject->id }}">
            <input type="hidden" name="score_mode" value="{{ $scoreMode }}">

            <!-- Score Grading System Info -->
            <div class="bg-blue-50 border border-blue-300 rounded-lg p-4 mb-6">
                <h3 class="font-bold text-blue-800 mb-2">📊 Grading System</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-blue-800 text-sm">
                    <div>
                        <span class="font-bold">1st Test:</span> 0-30
                    </div>
                    <div>
                        <span class="font-bold">Notes:</span> 0-10
                    </div>
                    <div>
                        <span class="font-bold">Exam:</span> 0-60
                    </div>
                </div>
                <p class="text-blue-800 text-sm mt-3">Total = 1st Test (30) + Notes (10) + Exam (60) = 100 marks</p>
            </div>

            <!-- Students Score Table -->
            @if($students->isEmpty())
                <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-6 text-center">
                    <p class="text-yellow-800 font-semibold">No students found in this class</p>
                </div>
            @else
                <div class="overflow-x-auto mb-6">
                    <table class="w-full border-collapse">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="border border-gray-300 px-4 py-3 text-left font-bold">S/N</th>
                                <th class="border border-gray-300 px-4 py-3 text-left font-bold">Student Name</th>
                                @if(in_array('ca1', $scoreFields, true))
                                    <th class="border border-gray-300 px-4 py-3 text-center font-bold">1st Test (30)</th>
                                @endif
                                @if(in_array('ca2', $scoreFields, true))
                                    <th class="border border-gray-300 px-4 py-3 text-center font-bold">Notes (10)</th>
                                @endif
                                @if(in_array('exam', $scoreFields, true))
                                    <th class="border border-gray-300 px-4 py-3 text-center font-bold">Exam (60)</th>
                                @endif
                                <th class="border border-gray-300 px-4 py-3 text-center font-bold">Total (100)</th>
                                <th class="border border-gray-300 px-4 py-3 text-center font-bold">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                                @php
                                    $existingScore = $scores[$student->id] ?? null;
                                    $ca1 = (float)($existingScore->ca1 ?? 0);
                                    $ca2 = (float)($existingScore->ca2 ?? 0);
                                    $exam = (float)($existingScore->exam ?? 0);
                                @endphp
                                <tr class="hover:bg-gray-50 border-b border-gray-300">
                                    <td class="border border-gray-300 px-4 py-3 text-center font-bold text-gray-700">{{ $index + 1 }}</td>
                                    <td class="border border-gray-300 px-4 py-3 font-semibold text-gray-800">
                                        {{ $student->name }}
                                        <br>
                                        <span class="text-sm text-gray-500">{{ $student->registration_number }}</span>
                                    </td>
                                    <input type="hidden" name="scores[{{ $index }}][student_id]" value="{{ $student->id }}">

                                    @if(in_array('ca1', $scoreFields, true))
                                        <td class="border border-gray-300 px-4 py-3">
                                            <input type="number" 
                                                name="scores[{{ $index }}][ca1]" 
                                                value="{{ $ca1 }}"
                                                min="0" max="30" step="0.5"
                                                class="w-full border border-gray-400 rounded px-2 py-1 text-center focus:outline-none focus:border-blue-500"
                                                placeholder="0">
                                        </td>
                                    @else
                                        <input type="hidden" name="scores[{{ $index }}][ca1]" value="{{ $ca1 }}">
                                    @endif

                                    @if(in_array('ca2', $scoreFields, true))
                                        <td class="border border-gray-300 px-4 py-3">
                                            <input type="number" 
                                                name="scores[{{ $index }}][ca2]" 
                                                value="{{ $ca2 }}"
                                                min="0" max="10" step="0.5"
                                                class="w-full border border-gray-400 rounded px-2 py-1 text-center focus:outline-none focus:border-blue-500"
                                                placeholder="0">
                                        </td>
                                    @else
                                        <input type="hidden" name="scores[{{ $index }}][ca2]" value="{{ $ca2 }}">
                                    @endif

                                    @if(in_array('exam', $scoreFields, true))
                                        <td class="border border-gray-300 px-4 py-3">
                                            <input type="number" 
                                                name="scores[{{ $index }}][exam]" 
                                                value="{{ $exam }}"
                                                min="0" max="60" step="0.5"
                                                class="w-full border border-gray-400 rounded px-2 py-1 text-center focus:outline-none focus:border-blue-500"
                                                placeholder="0">
                                        </td>
                                    @else
                                        <input type="hidden" name="scores[{{ $index }}][exam]" value="{{ $exam }}">
                                    @endif
                                    <td class="border border-gray-300 px-4 py-3 text-center font-bold bg-gray-100">
                                        <span class="total-score">{{ ($ca1 ?? 0) + ($ca2 ?? 0) + ($exam ?? 0) }}</span>
                                    </td>
                                    <td class="border border-gray-300 px-4 py-3 text-center">
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
                    <button type="button" onclick="submitForm('{{ route('teacher.scores.save') }}')" class="bg-yellow-600 hover:bg-yellow-700 text-white px-8 py-3 rounded-lg font-semibold">
                        💾 Save as Draft
                    </button>
                    <button type="button" onclick="submitForm('{{ route('teacher.scores.submit') }}')" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold">
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
// Calculate totals dynamically
document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('change', function() {
        const row = this.closest('tr');
        if (row) {
            const ca1 = parseFloat(row.querySelector('input[name*="[ca1]"]').value) || 0;
            const ca2 = parseFloat(row.querySelector('input[name*="[ca2]"]').value) || 0;
            const exam = parseFloat(row.querySelector('input[name*="[exam]"]').value) || 0;
            const total = ca1 + ca2 + exam;
            row.querySelector('.total-score').textContent = total.toFixed(1);
        }
    });
});

// Submit form with specific action
function submitForm(action) {
    const form = document.getElementById('scoresForm');
    form.action = action;
    form.submit();
}
</script>

@endsection
