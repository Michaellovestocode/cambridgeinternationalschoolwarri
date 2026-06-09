@extends('layouts.app')

@section('title', 'Academic Report Reviews')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Academic Report Reviews</h1>
            <p class="text-gray-600 mt-1">Review submitted report cards, edit scores where needed, approve, or return them to the form teacher.</p>
        </div>
        <a href="{{ route('admin.report-cards') }}" class="bg-gray-900 hover:bg-black text-white px-5 py-3 rounded-lg font-medium text-center">
            All Report Cards
        </a>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <form method="GET" action="{{ route('admin.report-cards.reviews') }}" class="grid md:grid-cols-3 gap-4">
            <div>
                <label for="session_id" class="block text-sm font-medium text-gray-700 mb-2">Session</label>
                <select id="session_id" name="session_id" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    @foreach ($sessions as $session)
                        <option value="{{ $session->id }}" @selected((string) request('session_id', $selectedSession?->id) === (string) $session->id)>
                            {{ $session->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="term_id" class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                <select id="term_id" name="term_id" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    @foreach ($terms as $term)
                        <option value="{{ $term->id }}" @selected((string) request('term_id', $selectedTerm?->id) === (string) $term->id)>
                            {{ $term->name }}{{ $term->session ? ' - ' . $term->session->name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-lg font-medium">
                    Filter
                </button>
                <a href="{{ route('admin.report-cards.reviews') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-3 rounded-lg font-medium">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-5">
        @forelse($reportCards as $reportCard)
            <div class="bg-white rounded-xl shadow p-5 space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">{{ $reportCard->student->name }}</h2>
                        <p class="text-sm text-gray-600">{{ $reportCard->class->display_name }} - {{ $reportCard->session->name }} - {{ $reportCard->term->name }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $reportCard->isSubmittedForReview() ? 'bg-orange-100 text-orange-800' : ($reportCard->isRejectedByReviewer() ? 'bg-red-100 text-red-800' : 'bg-emerald-100 text-emerald-800') }}">
                        {{ $reportCard->workflowLabel() }}
                    </span>
                </div>

                <div class="grid grid-cols-3 gap-3 text-sm">
                    <div class="rounded-lg bg-blue-50 p-3">
                        <p class="text-blue-700">Average</p>
                        <p class="font-bold text-blue-950">{{ number_format($reportCard->average_score, 1) }}%</p>
                    </div>
                    <div class="rounded-lg bg-purple-50 p-3">
                        <p class="text-purple-700">Grade</p>
                        <p class="font-bold text-purple-950">{{ $reportCard->overall_grade }}</p>
                    </div>
                    <div class="rounded-lg bg-amber-50 p-3">
                        <p class="text-amber-700">Position</p>
                        <p class="font-bold text-amber-950">{{ $reportCard->position }}/{{ $reportCard->total_students }}</p>
                    </div>
                </div>

                @if($reportCard->academic_rejection_reason)
                    <p class="rounded-lg bg-red-50 p-3 text-sm text-red-800">{{ $reportCard->academic_rejection_reason }}</p>
                @endif

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.report-cards.preview', $reportCard->id) }}" class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg text-sm font-medium">
                        Open Review
                    </a>
                    <a href="{{ route('admin.report-cards.visual-preview', $reportCard->id) }}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        Visual Preview
                    </a>
                </div>
            </div>
        @empty
            <div class="md:col-span-2 xl:col-span-3 bg-white rounded-xl shadow p-10 text-center text-gray-500">
                No report cards are waiting in the academic review queue.
            </div>
        @endforelse
    </div>

    <div>
        {{ $reportCards->links() }}
    </div>
</div>
@endsection
