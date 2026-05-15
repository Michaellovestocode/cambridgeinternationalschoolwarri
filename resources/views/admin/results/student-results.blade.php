@extends('layouts.app')

@section('title', $student->name . ' - Results')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-rose-600 to-rose-800 text-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">{{ $student->name }}</h1>
                <p class="text-rose-100 mt-1">
                    {{ $student->registration_number }} • 
                    {{ $student->class->display_name ?? 'N/A' }} • 
                    {{ $student->email }}
                </p>
            </div>
            <a href="{{ route('admin.results.index') }}" class="bg-rose-500 hover:bg-rose-600 text-white px-4 py-2 rounded-lg font-semibold">
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
        <div class="bg-purple-50 p-4 rounded-lg border-l-4 border-purple-500">
            <div class="text-sm text-gray-600">Average Score</div>
            <div class="text-3xl font-bold text-purple-600">{{ $statistics['average'] }}</div>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-500">
            <div class="text-sm text-gray-600">Best Score</div>
            <div class="text-3xl font-bold text-yellow-600">{{ $statistics['highest'] }}</div>
        </div>
    </div>

    <!-- Attempts Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-800">📋 Exam Attempts</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Exam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Date</th>
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $attempt->exam->title }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $attempt->exam->subject }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($attempt->submitted_at)
                                <div class="text-gray-900">{{ $attempt->submitted_at->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-600">{{ $attempt->submitted_at->format('H:i') }}</div>
                            @else
                                <span class="text-gray-500">—</span>
                            @endif
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
                                <div class="font-bold text-gray-900">{{ $attempt->total_score }}/{{ $attempt->exam->total_marks }}</div>
                            @else
                                <span class="text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($attempt->total_score !== null)
                                <div class="font-semibold text-indigo-600">
                                    {{ $attempt->exam->total_marks > 0 ? round(($attempt->total_score / $attempt->exam->total_marks) * 100, 1) : 0 }}%
                                </div>
                            @else
                                <span class="text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($attempt->status === 'graded')
                                @if($attempt->total_score >= $attempt->exam->pass_mark)
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
                            No attempts found for this student.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
