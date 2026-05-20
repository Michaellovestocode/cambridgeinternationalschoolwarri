@extends('layouts.app')

@section('title', 'Report Card')

@section('content')
<div class="bg-white rounded-3xl shadow-lg p-6 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm text-gray-500">{{ $reportCard->session?->name ?? 'Session' }} - {{ $reportCard->term?->name ?? 'Term' }}</p>
            <h1 class="text-3xl font-black text-gray-900">My Report Card</h1>
            <p class="text-sm text-gray-600">{{ $reportCard->class->display_name ?? 'Class not assigned' }}</p>
        </div>
        <a href="{{ route('student.dashboard') }}" class="text-sm text-blue-600 hover:underline">Back to dashboard</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-blue-50 rounded-2xl p-4">
            <p class="text-xs text-blue-700 font-semibold uppercase">Average</p>
            <p class="text-3xl font-black text-blue-900 mt-1">{{ $reportCard->average_score !== null ? number_format($reportCard->average_score, 1) . '%' : 'N/A' }}</p>
        </div>
        <div class="bg-green-50 rounded-2xl p-4">
            <p class="text-xs text-green-700 font-semibold uppercase">Grade</p>
            <p class="text-3xl font-black text-green-900 mt-1">{{ $reportCard->overall_grade ?? '-' }}</p>
        </div>
        <div class="bg-purple-50 rounded-2xl p-4">
            <p class="text-xs text-purple-700 font-semibold uppercase">Position</p>
            <p class="text-3xl font-black text-purple-900 mt-1">{{ $reportCard->position ?? '-' }}/{{ $reportCard->total_students ?? '-' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="border border-gray-100 rounded-2xl p-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Class Teacher Remark</h3>
            <p class="text-sm text-gray-700">{{ $reportCard->class_teacher_comment ?: 'No class teacher remark has been added yet.' }}</p>
        </div>
        <div class="border border-gray-100 rounded-2xl p-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Head Teacher Remark</h3>
            <p class="text-sm text-gray-700">{{ $reportCard->head_teacher_comment ?: 'No head teacher remark has been added yet.' }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 p-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Subject Scores</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-600">
                        <th class="px-4 py-3 text-left">Subject</th>
                        <th class="px-4 py-3 text-left">1st Test</th>
                        <th class="px-4 py-3 text-left">Notes</th>
                        <th class="px-4 py-3 text-left">Exam</th>
                        <th class="px-4 py-3 text-left">Total</th>
                        <th class="px-4 py-3 text-left">Grade</th>
                        <th class="px-4 py-3 text-left">Remark</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($scores as $score)
                        <tr class="border-t border-gray-100">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $score->subject->name ?? 'Subject' }}</td>
                            <td class="px-4 py-3">{{ number_format($score->ca1 ?? 0, 1) }}</td>
                            <td class="px-4 py-3">{{ number_format($score->ca2 ?? 0, 1) }}</td>
                            <td class="px-4 py-3">{{ number_format($score->exam ?? 0, 1) }}</td>
                            <td class="px-4 py-3">{{ number_format($score->total ?? 0, 1) }}</td>
                            <td class="px-4 py-3">{{ $score->grade ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $score->remark ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-500">No scores are attached to this report card yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
