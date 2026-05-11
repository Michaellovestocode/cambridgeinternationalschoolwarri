@extends('layouts.app')

@section('title', 'Class Results - ' . $class->name)

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-lg shadow p-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">{{ $class->name }} - Results</h2>
                <p class="text-gray-600">{{ $class->description }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('teacher.scores.dashboard') }}" 
                   class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
                    📥 Export PDF
                </a>
                <a href="{{ route('teacher.scores.dashboard') }}" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-3 rounded-lg font-semibold">
                    ← Back
                </a>
            </div>
        </div>

        <!-- Class Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4">
                <p class="text-gray-600 text-sm">Total Students</p>
                <p class="text-3xl font-bold text-blue-700">{{ $students->count() }}</p>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4">
                <p class="text-gray-600 text-sm">Total Exams</p>
                <p class="text-3xl font-bold text-purple-700">{{ $exams->count() }}</p>
            </div>
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4">
                <p class="text-gray-600 text-sm">Total Attempts</p>
                <p class="text-3xl font-bold text-orange-700">
                    @php
                        $totalAttempts = 0;
                        foreach($exams as $exam) {
                            $totalAttempts += count($resultMatrix[array_key_first($resultMatrix)][$exam->id] ?? []);
                        }
                    @endphp
                    {{ array_sum(array_map(function($r) use ($exams) { $c = 0; foreach($exams as $e) { if($r[$e->id]) $c++; } return $c; }, $resultMatrix)) }}
                </p>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4">
                <p class="text-gray-600 text-sm">Form Teacher</p>
                <p class="text-lg font-bold text-green-700">{{ $formTeacher->teacher->name }}</p>
            </div>
        </div>

        <!-- Results Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-green-500 to-green-600 text-white">
                        <th class="px-4 py-3 text-left font-semibold">Student Name</th>
                        <th class="px-4 py-3 text-left font-semibold">Roll No</th>
                        @foreach($exams as $exam)
                        <th class="px-4 py-3 text-center font-semibold text-sm">
                            <div class="font-bold">{{ Str::limit($exam->title, 15) }}</div>
                            <div class="text-xs font-normal text-green-100">{{ $exam->total_marks }} marks</div>
                        </th>
                        @endforeach
                        <th class="px-4 py-3 text-center font-semibold">Overall %</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                    @php
                        $studentData = $resultMatrix[$student->id];
                        $totalScore = 0;
                        $totalMarks = 0;
                        foreach($exams as $exam) {
                            if($studentData[$exam->id]) {
                                $totalScore += $studentData[$exam->id]['score'];
                                $totalMarks += $studentData[$exam->id]['total_marks'];
                            }
                        }
                        $overallPercentage = $totalMarks > 0 ? ($totalScore / $totalMarks) * 100 : 0;
                    @endphp
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $student->name }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $studentData['rollNumber'] }}</td>
                        @foreach($exams as $exam)
                        <td class="px-4 py-3 text-center">
                            @if($studentData[$exam->id])
                            <div class="font-bold text-gray-800">
                                {{ $studentData[$exam->id]['score'] }}/{{ $studentData[$exam->id]['total_marks'] }}
                            </div>
                            <div class="text-xs {{ $studentData[$exam->id]['status'] === 'Pass' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $studentData[$exam->id]['percentage'] }}%
                                <span class="font-semibold">{{ $studentData[$exam->id]['status'] }}</span>
                            </div>
                            @else
                            <span class="text-gray-400 text-sm">—</span>
                            @endif
                        </td>
                        @endforeach
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold text-lg {{ $overallPercentage >= 50 ? 'text-green-700' : 'text-red-700' }}">
                                {{ number_format($overallPercentage, 1) }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ 3 + $exams->count() }}" class="px-4 py-6 text-center text-gray-500">
                            No students in this class.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Legend -->
        <div class="mt-6 bg-gray-50 rounded-lg p-4">
            <p class="font-semibold text-gray-800 mb-2">Legend:</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="font-semibold text-green-600">Pass</span> - Score >= Pass Mark
                </div>
                <div>
                    <span class="font-semibold text-red-600">Fail</span> - Score < Pass Mark
                </div>
                <div>
                    <span class="font-semibold">—</span> - Not attempted
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
