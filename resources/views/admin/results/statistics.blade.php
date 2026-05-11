@extends('layouts.app')

@section('title', 'Results Statistics')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">📊 Results Statistics</h1>
                <p class="text-purple-100 mt-1">Comprehensive analysis and performance metrics</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.results.index') }}" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg font-semibold">
                    ← Back to Results
                </a>
            </div>
        </div>
    </div>

    <!-- Overall Statistics -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Overall Performance</h2>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg border-l-4 border-blue-500">
                <div class="text-sm text-gray-600 font-medium">Total Attempts</div>
                <div class="text-3xl font-bold text-blue-600 mt-2">{{ $overallStats['total_attempts'] }}</div>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-lg border-l-4 border-green-500">
                <div class="text-sm text-gray-600 font-medium">Graded</div>
                <div class="text-3xl font-bold text-green-600 mt-2">{{ $overallStats['graded'] }}</div>
                <div class="text-xs text-gray-600 mt-1">{{ $overallStats['total_attempts'] > 0 ? round(($overallStats['graded'] / $overallStats['total_attempts']) * 100, 1) : 0 }}%</div>
            </div>
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-4 rounded-lg border-l-4 border-yellow-500">
                <div class="text-sm text-gray-600 font-medium">Submitted</div>
                <div class="text-3xl font-bold text-yellow-600 mt-2">{{ $overallStats['submitted'] }}</div>
                <div class="text-xs text-gray-600 mt-1">Pending Grading</div>
            </div>
            <div class="bg-gradient-to-br from-red-50 to-red-100 p-4 rounded-lg border-l-4 border-red-500">
                <div class="text-sm text-gray-600 font-medium">In Progress</div>
                <div class="text-3xl font-bold text-red-600 mt-2">{{ $overallStats['in_progress'] }}</div>
                <div class="text-xs text-gray-600 mt-1">Not Started</div>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 p-4 rounded-lg border-l-4 border-indigo-500">
                <div class="text-sm text-gray-600 font-medium">Average Score</div>
                <div class="text-3xl font-bold text-indigo-600 mt-2">{{ $overallStats['average_score'] }}</div>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-lg border-l-4 border-purple-500">
                <div class="text-sm text-gray-600 font-medium">Highest Score</div>
                <div class="text-3xl font-bold text-purple-600 mt-2">{{ $overallStats['highest_score'] }}</div>
            </div>
            <div class="bg-gradient-to-br from-pink-50 to-pink-100 p-4 rounded-lg border-l-4 border-pink-500">
                <div class="text-sm text-gray-600 font-medium">Lowest Score</div>
                <div class="text-3xl font-bold text-pink-600 mt-2">{{ $overallStats['lowest_score'] }}</div>
            </div>
            <div class="bg-gradient-to-br from-teal-50 to-teal-100 p-4 rounded-lg border-l-4 border-teal-500">
                <div class="text-sm text-gray-600 font-medium">Pass Rate</div>
                <div class="text-3xl font-bold text-teal-600 mt-2">{{ $overallStats['pass_rate'] }}%</div>
            </div>
        </div>
    </div>

    <!-- Exam-wise Statistics -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-800">📝 Exam-wise Statistics</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Exam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Subject</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase">Attempts</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase">Graded</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase">Avg Score</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase">Pass Rate</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($examStats as $stat)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $stat['title'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $stat['subject'] }}</td>
                        <td class="px-6 py-4 text-center font-semibold text-gray-900">{{ $stat['attempts'] }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm font-semibold">{{ $stat['graded'] }}</span>
                        </td>
                        <td class="px-6 py-4 text-center font-semibold text-indigo-600">{{ $stat['average'] }}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center">
                                <div class="w-full bg-gray-200 rounded-full h-2 max-w-xs">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ $stat['pass_rate'] }}%"></div>
                                </div>
                                <span class="ml-2 font-semibold text-sm">{{ $stat['pass_rate'] }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('admin.results.exam-wise', $stat['id']) }}" class="text-blue-600 hover:text-blue-900 font-medium text-sm">
                                View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No exam data available.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Class-wise Statistics -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-800">👥 Class-wise Statistics</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-6">
            @forelse($classStats as $stat)
            <div class="border rounded-lg p-4 hover:shadow-lg transition">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="font-bold text-lg text-gray-800">{{ $stat['name'] }}</h3>
                    <a href="{{ route('admin.results.class-wise', $stat['id']) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                        View →
                    </a>
                </div>
                
                <div class="grid grid-cols-3 gap-2 text-sm">
                    <div class="bg-blue-50 p-2 rounded">
                        <div class="text-gray-600 text-xs">Students</div>
                        <div class="font-bold text-blue-600">{{ $stat['students'] }}</div>
                    </div>
                    <div class="bg-green-50 p-2 rounded">
                        <div class="text-gray-600 text-xs">Attempts</div>
                        <div class="font-bold text-green-600">{{ $stat['graded_count'] }}</div>
                    </div>
                    <div class="bg-purple-50 p-2 rounded">
                        <div class="text-gray-600 text-xs">Avg Score</div>
                        <div class="font-bold text-purple-600">{{ $stat['average'] }}</div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-2 text-center text-gray-500 py-8">
                No class data available.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Top Performers -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Top Performers Card -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b bg-gradient-to-r from-green-50 to-green-100">
                <h2 class="text-xl font-bold text-gray-800">🏆 Top Performers</h2>
            </div>

            <div class="divide-y">
                @forelse($topPerformers as $index => $performer)
                <div class="p-4 hover:bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center font-bold text-yellow-600">
                            #{{ $index + 1 }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 truncate">{{ $performer['name'] }}</p>
                            <p class="text-xs text-gray-600 truncate">{{ $performer['exam'] }}</p>
                        </div>
                        <div class="font-bold text-green-600">{{ $performer['score'] }}</div>
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-gray-500">
                    No data available.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b bg-gradient-to-r from-blue-50 to-blue-100">
                <h2 class="text-xl font-bold text-gray-800">📌 Recent Activity</h2>
            </div>

            <div class="divide-y max-h-96 overflow-y-auto">
                @forelse($recentActivity as $activity)
                <div class="p-4 hover:bg-gray-50">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            @switch($activity->status)
                                @case('in_progress')
                                    <span class="text-sm">📝</span>
                                    @break
                                @case('submitted')
                                    <span class="text-sm">✓</span>
                                    @break
                                @case('graded')
                                    <span class="text-sm">✅</span>
                                    @break
                            @endswitch
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900">{{ $activity->user->name }}</p>
                            <p class="text-xs text-gray-600">{{ $activity->exam->title }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $activity->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">
                                @switch($activity->status)
                                    @case('in_progress')
                                        In Progress
                                        @break
                                    @case('submitted')
                                        Submitted
                                        @break
                                    @case('graded')
                                        Graded
                                        @break
                                @endswitch
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-gray-500">
                    No recent activity.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
