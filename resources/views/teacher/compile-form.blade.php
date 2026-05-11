@extends('layouts.app')

@section('title', 'Manually Enter Result - ' . $class->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Manually Enter Result - {{ $class->name }}</h2>
                <p class="text-gray-600">Fill in exam results for your students</p>
            </div>
            <a href="{{ route('teacher.scores.dashboard') }}"
               class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-3 rounded-lg font-semibold">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Cancel
            </a>
        </div>
    </div>

    <!-- Form Disabled -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="bg-yellow-50 border-2 border-yellow-400 rounded-lg p-6 text-center">
            <p class="text-yellow-800 font-bold text-lg mb-4">⚠️ Form Compilation Feature Unavailable</p>
            <p class="text-yellow-700 mb-6">The manual result entry form is temporarily unavailable. Please use the Score Entry module to record student scores.</p>
            <a href="{{ route('teacher.scores.dashboard') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold">
                Go to Score Entry Dashboard
            </a>
        </div>
    </div>

    <!-- Original Form - Commented Out -->
    <!--
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Student <span class="text-red-500">*</span>
                    </label>
                    <select id="student_id" name="student_id" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('student_id') border-red-500 @enderror">
                        <option value="">-- Select Student --</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->name }} ({{ $student->registration_number }})
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="exam_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Exam <span class="text-red-500">*</span>
                    </label>
                    <select id="exam_id" name="exam_id" required onchange="updateTotalMarks()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('exam_id') border-red-500 @enderror">
                        <option value="">-- Select Exam --</option>
                        @foreach ($exams as $exam)
                            <option value="{{ $exam->id }}"
                                    data-total-marks="{{ $exam->total_marks }}"
                                    {{ old('exam_id') == $exam->id ? 'selected' : '' }}>
                                {{ $exam->title }} (Total: {{ $exam->total_marks }} marks)
                            </option>
                        @endforeach
                    </select>
                    @error('exam_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="score" class="block text-sm font-medium text-gray-700 mb-2">
                        Score Obtained <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="score" name="score" min="0" step="0.1" required
                           value="{{ old('score') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('score') border-red-500 @enderror"
                           placeholder="Enter score">
                    @error('score')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-sm mt-1">Maximum: <span id="maxMarks">-</span> marks</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Percentage</label>
                    <div class="relative">
                        <input type="text" id="percentage" readonly placeholder="0%"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                        <span class="absolute right-3 top-2 text-gray-500">%</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Save Result
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Results -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-4 border-b border-gray-100">
            <h3 class="text-xl font-bold text-gray-800 flex items-center">
                <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Recently Entered Results
            </h3>
        </div>
        <div class="p-6">
            @php
                $recentAttempts = \App\Models\ExamAttempt::whereIn('user_id', $students->pluck('id'))
                    ->whereIn('exam_id', $exams->pluck('id'))
                    ->orderBy('updated_at', 'desc')
                    ->limit(10)
                    ->get();
            @endphp

            @if ($recentAttempts->isEmpty())
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-gray-500">No results entered yet.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Exam</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Score</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Percentage</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Date Entered</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($recentAttempts as $attempt)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-semibold text-gray-800">{{ $attempt->user->name }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-800">{{ $attempt->exam->title }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-semibold">{{ $attempt->total_score }}/{{ $attempt->exam->total_marks }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $percentage = $attempt->exam->total_marks > 0 ? round(($attempt->total_score / $attempt->exam->total_marks) * 100, 1) : 0;
                                        @endphp
                                        <span class="font-semibold text-gray-800">{{ $percentage }}%</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $attempt->updated_at->format('d M Y') }}
                                        <div class="text-gray-400">{{ $attempt->updated_at->format('H:i') }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    -->
</div>

<script>
function updateTotalMarks() {
    const examSelect = document.getElementById('exam_id');
    const selectedOption = examSelect.options[examSelect.selectedIndex];
    const totalMarks = selectedOption.getAttribute('data-total-marks') || '-';
    document.getElementById('maxMarks').textContent = totalMarks;

    // Reset score and percentage
    document.getElementById('score').value = '';
    document.getElementById('percentage').value = '';
}

// Calculate percentage on score input
document.getElementById('score').addEventListener('input', function() {
    const examSelect = document.getElementById('exam_id');
    const selectedOption = examSelect.options[examSelect.selectedIndex];
    const totalMarks = parseInt(selectedOption.getAttribute('data-total-marks')) || 0;
    const score = parseFloat(this.value) || 0;

    if (totalMarks > 0) {
        const percentage = ((score / totalMarks) * 100).toFixed(2);
        document.getElementById('percentage').value = percentage;
    } else {
        document.getElementById('percentage').value = '';
    }
});

// Initialize on page load
window.addEventListener('load', updateTotalMarks);
</script>
@endsection
