@extends('layouts.app')

@section('title', 'Class Attempts')

@section('content')
<div class="space-y-6">
    <div class="rounded-3xl bg-gradient-to-r from-blue-600 to-indigo-700 p-6 text-white shadow-xl">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-white/70">Class Attempts</p>
                <h1 class="mt-2 text-3xl font-black">{{ $class->display_name }}</h1>
                <p class="mt-2 text-white/85">Review submitted and graded attempts for this class.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex rounded-2xl bg-white px-5 py-3 text-sm font-black text-indigo-700 shadow-lg hover:bg-indigo-50">Back to Dashboard</a>
        </div>
    </div>

    <div class="overflow-hidden rounded-3xl bg-white shadow-lg">
        <div class="border-b border-gray-100 px-6 py-4">
            <h2 class="text-xl font-black text-gray-900">Student Attempts</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $attempts->total() }} attempt{{ $attempts->total() === 1 ? '' : 's' }} found</p>
        </div>

        <div class="overflow-x-auto">
            @if($attempts->count() > 0)
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-wider text-gray-500">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-wider text-gray-500">Exam</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-wider text-gray-500">Score</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-wider text-gray-500">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-wider text-gray-500">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($attempts as $attempt)
                            <tr>
                                <td class="px-6 py-4">
                                    <p class="font-black text-gray-900">{{ $attempt->user->name }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ $attempt->user->registration_number }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-gray-800">{{ $attempt->exam->title }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ $attempt->exam->subject }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    @if($attempt->status === \App\Models\ExamAttempt::STATUS_GRADED)
                                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-black text-green-800">Graded</span>
                                    @elseif($attempt->status === \App\Models\ExamAttempt::STATUS_SUBMITTED)
                                        <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-black text-yellow-800">Pending</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-black text-gray-700">{{ ucfirst($attempt->status) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-700">
                                    {{ $attempt->total_score !== null ? $attempt->total_score . ' marks' : '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $attempt->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($attempt->status === \App\Models\ExamAttempt::STATUS_SUBMITTED)
                                        <a href="{{ route('admin.attempt.grade', $attempt) }}" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-black text-white hover:bg-blue-700">Grade</a>
                                    @else
                                        <a href="{{ route('admin.exam.results', $attempt->exam_id) }}" class="text-sm font-black text-blue-600 hover:text-blue-800">View Results</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="px-6 py-12 text-center text-gray-500">
                    No attempts have been recorded for this class yet.
                </div>
            @endif
        </div>

        @if($attempts->hasPages())
            <div class="border-t border-gray-100 px-6 py-4">
                {{ $attempts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
