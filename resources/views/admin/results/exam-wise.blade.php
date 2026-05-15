@extends('layouts.app')

@section('title', $exam->title . ' - Results')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 text-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">{{ $exam->title }}</h1>
                <p class="text-indigo-100 mt-1">{{ $exam->subject }} • Exam Results View</p>
            </div>
            <a href="{{ route('admin.results.index') }}" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg font-semibold">
                ← Back to Results Portal
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
            <div class="text-sm text-gray-600">Total Attempts</div>
            <div class="text-3xl font-bold text-blue-600">{{ $statistics['total_attempts'] }}</div>
        </div>
        <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
            <div class="text-sm text-gray-600">Graded</div>
            <div class="text-3xl font-bold text-green-600">{{ $statistics['graded'] }}</div>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-500">
            <div class="text-sm text-gray-600">Pending</div>
            <div class="text-3xl font-bold text-yellow-600">{{ $statistics['submitted'] }}</div>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg border-l-4 border-purple-500">
            <div class="text-sm text-gray-600">Pass Rate</div>
            <div class="text-3xl font-bold text-purple-600">{{ $statistics['pass_rate'] }}%</div>
        </div>
    </div>

    @if($statistics['graded'] > 0)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-indigo-50 p-4 rounded-lg border-l-4 border-indigo-500">
            <div class="text-sm text-gray-600">Average Score</div>
            <div class="text-2xl font-bold text-indigo-600">{{ $statistics['average'] }}/{{ $exam->total_marks }}</div>
        </div>
        <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
            <div class="text-sm text-gray-600">Highest Score</div>
            <div class="text-2xl font-bold text-green-600">{{ $statistics['highest'] }}/{{ $exam->total_marks }}</div>
        </div>
        <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-500">
            <div class="text-sm text-gray-600">Lowest Score</div>
            <div class="text-2xl font-bold text-red-600">{{ $statistics['lowest'] }}/{{ $exam->total_marks }}</div>
        </div>
    </div>
    @endif

    <!-- Results Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-800">📋 Student Results</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Student Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Registration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Class</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Percentage</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Result</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($attempts as $index => $attempt)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $attempt->user->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $attempt->user->registration_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            {{ $attempt->user->class->display_name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @switch($attempt->status)
                                @case('in_progress')
                                    <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-semibold">In Progress</span>
                                    @break
                                @case('submitted')
                                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">Submitted</span>
                                    @break
                                @case('graded')
                                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">Graded</span>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($attempt->total_score !== null)
                                <div class="font-bold text-gray-900">{{ $attempt->total_score }}/{{ $exam->total_marks }}</div>
                            @else
                                <span class="text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($attempt->total_score !== null)
                                <div class="font-semibold text-indigo-600">
                                    {{ $exam->total_marks > 0 ? round(($attempt->total_score / $exam->total_marks) * 100, 1) : 0 }}%
                                </div>
                            @else
                                <span class="text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($attempt->status === 'graded')
                                @if($attempt->total_score >= $exam->pass_mark)
                                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">✓ Pass</span>
                                @else
                                    <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">✗ Fail</span>
                                @endif
                            @else
                                <span class="text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex gap-2">
                                <a href="{{ route('admin.results.student', $attempt->user->id) }}" class="text-blue-600 hover:text-blue-900 font-medium">
                                    View
                                </a>
                                @if(in_array($attempt->status, ['submitted', 'graded']))
                                    <a href="{{ route('admin.attempt.grade', $attempt->id) }}" class="text-orange-600 hover:text-orange-900 font-medium">
                                        {{ $attempt->status === 'graded' ? 'View/Edit' : 'Grade' }}
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                            No results found for this exam.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
