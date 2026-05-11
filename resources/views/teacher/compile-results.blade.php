@extends('layouts.app')

@section('title', 'Compile Results - ' . $class->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Compile Results - {{ $class->name }}</h2>
                <p class="text-gray-600">View and manage exam results for {{ $class->name }}</p>
            </div>
            <div class="flex gap-3">
                <!-- <a href="{{ route('teacher.form-teacher.compile-form', $class->id) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Manually Enter Result
                </a> -->
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-6 py-3 rounded-lg font-semibold">
                    ⚠️ This feature is temporarily unavailable. Use Score Entry to record scores.
                </div>
                <a href="{{ route('teacher.scores.dashboard') }}"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-3 rounded-lg font-semibold">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    @if ($students->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <div class="flex items-center">
                <svg class="w-8 h-8 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-yellow-800">No Students Found</h3>
                    <p class="text-yellow-700">No students have been added to this class yet.</p>
                    <a href="{{ route('teacher.scores.dashboard') }}"
                       class="text-yellow-800 font-semibold underline hover:text-yellow-900 mt-2 inline-block">
                        Add students now →
                    </a>
                </div>
            </div>
        </div>
    @elseif ($exams->isEmpty())
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex items-center">
                <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-blue-800">No Exams Found</h3>
                    <p class="text-blue-700">No exams have been created for this class yet.</p>
                </div>
            </div>
        </div>
    @else
        <!-- Results Matrix -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 px-6 py-4 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-800 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Class Results Matrix
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Student Name</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Roll Number</th>
                            @foreach ($exams as $exam)
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    {{ substr($exam->title, 0, 15) }}
                                    <br>
                                    <span class="text-gray-500 font-normal">(Out of {{ $exam->total_marks }})</span>
                                </th>
                            @endforeach
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Average Score</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($resultMatrix as $studentId => $result)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-gray-800">{{ $result['name'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-gray-600">{{ $result['rollNumber'] }}</div>
                                </td>

                                @php
                                    $totalScore = 0;
                                    $examCount = 0;
                                @endphp

                                @foreach ($result['exams'] as $examId => $examResult)
                                    <td class="px-6 py-4 text-center">
                                        @if ($examResult['has_attempt'])
                                            <div class="inline-flex flex-col items-center">
                                                <span class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full font-semibold">
                                                    {{ $examResult['score'] }}/{{ $examResult['total_marks'] }}
                                                </span>
                                                <span class="text-xs text-gray-500 mt-1">
                                                    {{ round(($examResult['score'] / $examResult['total_marks']) * 100, 1) }}%
                                                </span>
                                            </div>
                                            @php
                                                $totalScore += $examResult['score'];
                                                $examCount++;
                                            @endphp
                                        @else
                                            <span class="px-3 py-1 bg-gray-100 text-gray-800 text-sm rounded-full font-semibold">Pending</span>
                                        @endif
                                    </td>
                                @endforeach

                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    @if ($examCount > 0)
                                        <span class="font-bold text-gray-800">{{ round($totalScore / $examCount, 1) }}%</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-3xl font-bold text-blue-600 mb-2">{{ $students->count() }}</div>
                <div class="text-gray-600 font-semibold">Total Students</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-3xl font-bold text-green-600 mb-2">{{ $exams->count() }}</div>
                <div class="text-gray-600 font-semibold">Total Exams</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-3xl font-bold text-purple-600 mb-2">
                    @php
                        $totalAttempts = 0;
                        foreach ($resultMatrix as $result) {
                            foreach ($result['exams'] as $exam) {
                                if ($exam['has_attempt']) $totalAttempts++;
                            }
                        }
                    @endphp
                    {{ $totalAttempts }}
                </div>
                <div class="text-gray-600 font-semibold">Completed Results</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-3xl font-bold text-red-600 mb-2">
                    @php
                        $totalPending = ($students->count() * $exams->count()) - $totalAttempts;
                    @endphp
                    {{ $totalPending }}
                </div>
                <div class="text-gray-600 font-semibold">Pending Results</div>
            </div>
        </div>
    @endif
</div>
@endsection
