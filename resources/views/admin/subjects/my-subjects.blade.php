@extends('layouts.app')

@section('title', 'My Subjects')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 to-green-800 text-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">📚 My Subjects</h1>
                <p class="text-green-100 mt-1">View subjects assigned to you</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="bg-white text-green-600 hover:bg-green-50 px-6 py-2 rounded-lg font-semibold">
                ← Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Subjects Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-800">Assigned Subjects</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Code</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase">Classes</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase">Students</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase">Exams</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase">Performance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($subjects as $index => $subject)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $subject->name }}</div>
                            @if($subject->description)
                                <div class="text-xs text-gray-600">{{ Str::limit($subject->description, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($subject->code)
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    {{ $subject->code }}
                                </span>
                            @else
                                <span class="text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="text-sm">
                                <div class="font-semibold text-purple-600">{{ $subject->classes->count() }}</div>
                                <div class="text-xs text-gray-500">classes</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="text-sm">
                                <div class="font-semibold text-indigo-600">{{ $subject->total_students }}</div>
                                <div class="text-xs text-gray-500">students</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="text-sm">
                                <div class="font-semibold text-green-600">{{ $subject->exams_count }}</div>
                                @if($subject->upcoming_exams_count > 0)
                                    <div class="text-xs text-orange-600">{{ $subject->upcoming_exams_count }} upcoming</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($subject->average_performance !== null)
                                <div class="text-sm">
                                    <div class="font-semibold {{ $subject->average_performance >= 70 ? 'text-green-600' : ($subject->average_performance >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ $subject->average_performance }}%
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $subject->recent_attempts_count }} attempts</div>
                                </div>
                            @else
                                <span class="text-gray-500 text-sm">No data</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($subject->is_active)
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    Active
                                </span>
                            @else
                                <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-wrap gap-1">
                                <a href="{{ route('admin.exam.create') }}"
                                   class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs font-medium">
                                    Create Exam
                                </a>
                                <a href="{{ route('admin.exams') }}"
                                   class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs font-medium">
                                    View Exams
                                </a>
                                <a href="{{ route('admin.results.index') }}"
                                   class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded text-xs font-medium">
                                    Results
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                            No subjects assigned to you yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Activity & Exam Summary -->
    @if($subjects->count() > 0)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <!-- Recent Exams -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b">
                <h3 class="text-lg font-bold text-gray-800">📝 Recent Exams</h3>
            </div>
            <div class="p-6">
                @php
                    $recentExams = collect();
                    foreach($subjects as $subject) {
                        foreach($subject->exams->take(2) as $exam) {
                            $recentExams->push([
                                'exam' => $exam,
                                'subject' => $subject
                            ]);
                        }
                    }
                    $recentExams = $recentExams->sortByDesc('exam.created_at')->take(5);
                @endphp

                @if($recentExams->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentExams as $item)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">{{ $item['exam']->title }}</div>
                                <div class="text-sm text-gray-600">{{ $item['subject']->name }} • {{ $item['exam']->created_at->diffForHumans() }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($item['exam']->start_time > now())
                                    <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs">Upcoming</span>
                                @elseif($item['exam']->end_time < now())
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Completed</span>
                                @else
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Active</span>
                                @endif
                                <a href="{{ route('admin.exam.results', $item['exam']->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">No recent exams found.</p>
                @endif
            </div>
        </div>

        <!-- Performance Overview -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b">
                <h3 class="text-lg font-bold text-gray-800">📊 Performance Overview</h3>
            </div>
            <div class="p-6">
                @php
                    $totalSubjects = $subjects->count();
                    $activeSubjects = $subjects->where('is_active', true)->count();
                    $totalExams = $subjects->sum('exams_count');
                    $totalStudents = $subjects->sum('total_students');
                    $avgPerformance = $subjects->whereNotNull('average_performance')->avg('average_performance');
                @endphp

                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">{{ $totalSubjects }}</div>
                        <div class="text-sm text-gray-600">Total Subjects</div>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">{{ $activeSubjects }}</div>
                        <div class="text-sm text-gray-600">Active Subjects</div>
                    </div>
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">{{ $totalExams }}</div>
                        <div class="text-sm text-gray-600">Total Exams</div>
                    </div>
                    <div class="text-center p-4 bg-indigo-50 rounded-lg">
                        <div class="text-2xl font-bold text-indigo-600">{{ $totalStudents }}</div>
                        <div class="text-sm text-gray-600">Total Students</div>
                    </div>
                </div>

                @if($avgPerformance)
                <div class="mt-4 p-4 bg-gradient-to-r from-green-50 to-blue-50 rounded-lg">
                    <div class="text-center">
                        <div class="text-3xl font-bold {{ $avgPerformance >= 70 ? 'text-green-600' : ($avgPerformance >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                            {{ number_format($avgPerformance, 1) }}%
                        </div>
                        <div class="text-sm text-gray-600">Average Performance</div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif