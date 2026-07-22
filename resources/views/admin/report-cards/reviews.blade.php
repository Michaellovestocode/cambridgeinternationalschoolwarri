@extends('layouts.app')

@section('title', 'Academic Report Reviews')

@section('content')
@php
    $formatOrdinal = function ($value) {
        if ($value === null || $value === '') {
            return new \Illuminate\Support\HtmlString('');
        }

        $position = (int) $value;
        $suffix = 'th';

        if ($position % 100 < 11 || $position % 100 > 13) {
            $suffix = match ($position % 10) {
                1 => 'st',
                2 => 'nd',
                3 => 'rd',
                default => 'th',
            };
        }

        return new \Illuminate\Support\HtmlString($position . '<sup class="text-[65%] leading-none align-super">' . $suffix . '</sup>');
    };
@endphp
<div class="max-w-7xl mx-auto space-y-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Academic Report Reviews</h1>
            <p class="text-gray-600 mt-1">Review submitted report cards, edit scores where needed, approve, or return them to the form teacher.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.report-cards') }}" class="bg-gray-900 hover:bg-black text-white px-5 py-3 rounded-lg font-medium text-center">
                All Report Cards
            </a>
            @if(auth()->user()->isAdmin() || auth()->user()->canReviewReportCards())
                <a href="{{ route('admin.developmental-reports.index', array_filter([ 'class_id' => request('class_id'), 'session_id' => request('session_id', $selectedSession?->id), 'term_id' => request('term_id', $selectedTerm?->id), ])) }}" class="bg-teal-500 hover:bg-teal-600 text-white px-5 py-3 rounded-lg font-medium text-center">
                    Development
                </a>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <form method="GET" action="{{ route('admin.report-cards.reviews') }}" class="grid md:grid-cols-4 gap-4">
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
            <div>
                <label for="class_id" class="block text-sm font-medium text-gray-700 mb-2">Class</label>
                <select id="class_id" name="class_id" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    <option value="">Choose from cards</option>
                    @foreach ($reviewClasses as $class)
                        <option value="{{ $class->id }}" @selected((string) request('class_id') === (string) $class->id)>
                            {{ $class->display_name }}
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

    @unless($selectedClass)
    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-5">
        @forelse($reviewClasses as $class)
            @php
                $classUrl = route('admin.report-cards.reviews', array_filter([
                    'session_id' => request('session_id', $selectedSession?->id),
                    'term_id' => request('term_id', $selectedTerm?->id),
                    'class_id' => $class->id,
                ]));
                $isSelectedClass = $selectedClass && (int) $selectedClass->id === (int) $class->id;
            @endphp
            <a href="{{ $classUrl }}" class="block rounded-xl border {{ $isSelectedClass ? 'border-blue-500 ring-4 ring-blue-100' : 'border-gray-100' }} bg-white p-5 shadow hover:shadow-lg transition">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">{{ $class->display_name }}</h2>
                        <p class="text-sm text-gray-500">{{ $class->section_label }}</p>
                    </div>
                    <span class="rounded-full bg-gray-900 px-3 py-1 text-xs font-bold text-white">{{ $class->learner_total }} learners</span>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3 text-sm sm:grid-cols-5">
                    <div class="rounded-lg bg-gray-50 p-3">
                        <p class="text-gray-700">Not Submitted</p>
                        <p class="font-bold text-gray-950">{{ $class->not_submitted_count }}</p>
                    </div>
                    <div class="rounded-lg bg-orange-50 p-3">
                        <p class="text-orange-700">Submitted</p>
                        <p class="font-bold text-orange-950">{{ $class->review_submitted_count }}</p>
                    </div>
                    <div class="rounded-lg bg-red-50 p-3">
                        <p class="text-red-700">Returned</p>
                        <p class="font-bold text-red-950">{{ $class->review_rejected_count }}</p>
                    </div>
                    <div class="rounded-lg bg-emerald-50 p-3">
                        <p class="text-emerald-700">Approved</p>
                        <p class="font-bold text-emerald-950">{{ $class->review_approved_count }}</p>
                    </div>
                    <div class="rounded-lg bg-indigo-50 p-3">
                        <p class="text-indigo-700">Published</p>
                        <p class="font-bold text-indigo-950">{{ $class->review_published_count }}</p>
                    </div>
                </div>
            </a>
        @empty
            <div class="md:col-span-2 xl:col-span-3 bg-white rounded-xl shadow p-10 text-center text-gray-500">
                No classes are assigned to you for academic review.
            </div>
        @endforelse
    </div>
    @endunless

    @if($selectedClass)
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $selectedClass->display_name }} Report Cards</h2>
                <p class="text-gray-600 mt-1">{{ $selectedClassLearners->count() }} learners are shown, including learners not submitted yet.</p>
            </div>
            <a href="{{ route('admin.report-cards.reviews', array_filter([
                'session_id' => request('session_id', $selectedSession?->id),
                'term_id' => request('term_id', $selectedTerm?->id),
            ])) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg font-medium text-center">
                Change Class
            </a>
            <form method="POST" action="{{ route('admin.report-cards.bulk-approve-review') }}" onsubmit="return confirm('Approve all ready submitted report cards in this class?')">
                @csrf
                @method('PUT')
                <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                <input type="hidden" name="session_id" value="{{ request('session_id', $selectedSession?->id) }}">
                <input type="hidden" name="term_id" value="{{ request('term_id', $selectedTerm?->id) }}">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-medium">
                    Approve All Ready
                </button>
            </form>
        </div>
    @else
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-6 text-blue-900">
            Select a class card above to review its report cards.
        </div>
    @endif

    <div class="space-y-3">
        @forelse($reviewRows as $row)
            @php($reportCard = $row->reportCard)
            <div class="rounded-xl bg-white p-4 shadow">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <h2 class="break-words text-base font-bold text-gray-900">{{ $row->learner->name }}</h2>
                        <p class="text-sm text-gray-600">{{ $row->learner->registration_number ?: 'No registration number' }}</p>
                    </div>
                    @if($reportCard)
                        <span class="w-fit px-3 py-1 rounded-full text-xs font-semibold {{ $reportCard->isSubmittedForReview() ? 'bg-orange-100 text-orange-800' : ($reportCard->isRejectedByReviewer() ? 'bg-red-100 text-red-800' : 'bg-emerald-100 text-emerald-800') }}">
                            {{ $reportCard->workflowLabel() }}
                        </span>
                    @else
                        <span class="w-fit rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">Not Submitted</span>
                    @endif
                </div>

                @if($reportCard)
                    <div class="mt-4 grid grid-cols-3 gap-2 text-sm">
                        <div class="rounded-lg bg-blue-50 p-3">
                            <p class="text-blue-700">Average</p>
                            <p class="font-bold text-blue-950">{{ number_format($reportCard->computed_average_score ?? $reportCard->average_score ?? 0, 1) }}%</p>
                        </div>
                        <div class="rounded-lg bg-purple-50 p-3">
                            <p class="text-purple-700">Grade</p>
                            <p class="font-bold text-purple-950">{{ $reportCard->computed_overall_grade ?? ($reportCard->overall_grade ?? '-') }}</p>
                        </div>
                        @if($reportCard->position)
                            <div class="rounded-lg bg-amber-50 p-3">
                                <p class="text-amber-700">Position</p>
                                <p class="font-bold text-amber-950">{!! $formatOrdinal($reportCard->position) !!}</p>
                            </div>
                        @endif
                    </div>

                    @if($reportCard->academic_rejection_reason)
                        <p class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-800">{{ $reportCard->academic_rejection_reason }}</p>
                    @endif

                    <div class="mt-4 grid gap-2 sm:flex sm:flex-wrap">
                        <a href="{{ route('admin.report-cards.preview', $reportCard->id) }}" class="bg-gray-900 hover:bg-black text-white px-4 py-3 rounded-lg text-center text-sm font-medium">
                            Open Review
                        </a>
                        <a href="{{ route('admin.report-cards.visual-preview', $reportCard->id) }}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg text-center text-sm font-medium">
                            Visual Preview
                        </a>
                    </div>
                @else
                    <p class="mt-3 rounded-lg bg-gray-50 p-3 text-sm font-semibold text-gray-600">
                        This learner does not have a report card submitted for this session and term.
                    </p>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-xl shadow p-10 text-center text-gray-500">
                {{ $selectedClass ? 'No report cards are waiting in this class review queue.' : 'Choose a class above to open its review queue.' }}
            </div>
        @endforelse
    </div>
</div>
@endsection
