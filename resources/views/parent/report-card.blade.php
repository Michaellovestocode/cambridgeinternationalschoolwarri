@extends('layouts.app')

@section('title', 'Report Card')

@section('content')
<div class="bg-white rounded-3xl shadow-lg p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">{{ $reportCard->session?->name ?? 'Session' }} - {{ $reportCard->term?->name ?? 'Term' }}</p>
            <h1 class="text-3xl font-black text-gray-900">{{ $reportCard->student->name }}</h1>
            <p class="text-sm text-gray-600">{{ $reportCard->class->display_name ?? 'Class not assigned' }}</p>
        </div>
        <a href="{{ route('parent.dashboard') }}" class="text-sm text-blue-600 hover:underline">Back to portal</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Report Summary</h3>
            <p class="text-sm text-gray-700">
                {{ $reportCard->class_teacher_comment ?: $reportCard->head_teacher_comment ?: 'No teacher remark has been added yet.' }}
            </p>
        </div>
        <div class="bg-gray-50 p-4 rounded-2xl">
            <p class="text-xs text-gray-500">Total Average</p>
            <p class="text-3xl font-bold text-gray-900">
                {{ $reportCard->average_score !== null ? number_format($reportCard->average_score, 1) . '%' : 'N/A' }}
            </p>
            <p class="text-sm text-gray-600 mt-1">Grade: {{ $reportCard->overall_grade ?? '-' }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 p-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Detailed Scores</h3>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">No scores are attached to this report card yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
