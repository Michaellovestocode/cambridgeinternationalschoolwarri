@extends('layouts.app')

@section('title', 'Exam Results')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $exam->title }} - Results</h2>
                <p class="text-gray-600">{{ $exam->subject }}</p>
            </div>
            <a href="{{ route('admin.exams') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                ← Back to Exams
            </a>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Total Students</div>
                <div class="text-2xl font-bold text-blue-600">{{ $statistics['total_students'] }}</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Graded</div>
                <div class="text-2xl font-bold text-green-600">{{ $statistics['graded'] }}</div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Pending</div>
                <div class="text-2xl font-bold text-yellow-600">{{ $statistics['pending'] }}</div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Pass Rate</div>
                <div class="text-2xl font-bold text-purple-600">{{ $statistics['pass_rate'] }}%</div>
            </div>
        </div>

        @if($statistics['graded'] > 0)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Average Score</div>
                <div class="text-xl font-bold text-gray-800">{{ $statistics['average'] }}/{{ $exam->total_marks }}</div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Highest Score</div>
                <div class="text-xl font-bold text-green-600">{{ $statistics['highest'] }}/{{ $exam->total_marks }}</div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Lowest Score</div>
                <div class="text-xl font-bold text-red-600">{{ $statistics['lowest'] }}/{{ $exam->total_marks }}</div>
            </div>
        </div>
        @endif

        <div class="mt-6 border-t pt-5">
            <div class="flex flex-wrap items-center justify-between gap-4 bg-gray-50 rounded-lg p-4">
                <div>
                    <h3 class="font-bold text-gray-800">Student Result Release</h3>
                    <p class="text-sm text-gray-600">
                        @if($exam->show_results_to_students)
                            Students can view their scores, pass/fail status, and answer scripts.
                        @else
                            Students only see that the exam was submitted successfully.
                        @endif
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.exam.result-release', $exam->id) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="show_results_to_students" value="{{ $exam->show_results_to_students ? 0 : 1 }}">
                    <button type="submit"
                            class="{{ $exam->show_results_to_students ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white px-5 py-2 rounded font-semibold">
                        {{ $exam->show_results_to_students ? 'Hide From Students' : 'Release To Students' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Export Buttons -->
        <div class="flex gap-3 mt-6">
            <a href="{{ route('admin.exam.export.pdf', $exam->id) }}" 
               class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded font-semibold">
                📄 Export PDF
            </a>
            <a href="{{ route('admin.exam.export.word', $exam->id) }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded font-semibold">
                📝 Export Word
            </a>
        </div>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b">
            <h3 class="text-xl font-bold text-gray-800">Student Results</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($attempts as $index => $attempt)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $attempt->user->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $attempt->user->registration_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $attempt->user->class->display_name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $attempt->submitted_at ? $attempt->submitted_at->format('d M Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($attempt->status === 'graded')
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Graded</span>
                            @elseif($attempt->status === 'submitted')
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Pending</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">In Progress</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($attempt->total_score !== null)
                                <span class="font-semibold {{ $attempt->total_score >= $exam->pass_mark ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $attempt->total_score }}/{{ $exam->total_marks }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex gap-3 items-center">
                                @if($attempt->status === 'in_progress')
                                    <!-- Submit For Student Button -->
                                    <form method="POST" 
                                          action="{{ route('admin.submit-for-student', $attempt->id) }}" 
                                          onsubmit="return confirm('⚠️ Submit exam for {{ $attempt->user->name }}?\n\nThis will:\n✓ Mark exam as completed\n✓ Auto-grade MCQ/Fill-blank\n✓ Stop the timer\n\nCannot be undone!')"
                                          class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs font-semibold">
                                            ✓ Submit
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.attempt.grade', $attempt->id) }}" 
                                       class="text-gray-400 hover:text-gray-600 text-xs" title="View Progress">
                                        👁️ View
                                    </a>
                                @elseif($attempt->status === 'submitted')
                                    <a href="{{ route('admin.attempt.grade', $attempt->id) }}" 
                                       class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs font-semibold">
                                        📝 Grade Now
                                    </a>
                                @elseif($attempt->status === 'graded')
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.attempt.print', $attempt->id) }}" 
                                           class="text-purple-600 hover:text-purple-800 text-lg" title="Print Script">
                                            🖨️
                                        </a>
                                        <a href="{{ route('admin.attempt.grade', $attempt->id) }}" 
                                           class="text-green-600 hover:text-green-800 text-lg" title="View/Edit">
                                            👁️
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            No student attempts yet
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
