@extends('layouts.app')

@section('title', 'Class Rankings')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="rounded-2xl bg-white p-5 shadow">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-black text-gray-900">Class Rankings</h1>
                <p class="mt-1 text-sm font-semibold text-gray-500">Learners are arranged from first position downward for the selected term.</p>
            </div>
            <a href="{{ route('teacher.scores.dashboard') }}" class="rounded-xl bg-gray-900 px-5 py-3 text-center text-sm font-black text-white hover:bg-black">
                Back to Scores
            </a>
        </div>
    </div>

    <div class="rounded-2xl bg-white p-5 shadow">
        <form method="GET" action="{{ route('teacher.scores.class-rankings') }}" class="grid gap-4 md:grid-cols-4">
            <div>
                <label for="class_id" class="mb-2 block text-sm font-bold text-gray-700">Class</label>
                <select id="class_id" name="class_id" class="w-full rounded-xl border border-gray-300 px-4 py-3">
                    @foreach($formClasses as $class)
                        <option value="{{ $class->id }}" @selected((int) $selectedClass?->id === (int) $class->id)>
                            {{ $class->display_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="session_id" class="mb-2 block text-sm font-bold text-gray-700">Session</label>
                <select id="session_id" name="session_id" class="w-full rounded-xl border border-gray-300 px-4 py-3">
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}" @selected((int) $selectedSession?->id === (int) $session->id)>
                            {{ $session->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="term_id" class="mb-2 block text-sm font-bold text-gray-700">Term</label>
                <select id="term_id" name="term_id" class="w-full rounded-xl border border-gray-300 px-4 py-3">
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}" @selected((int) $selectedTerm?->id === (int) $term->id)>
                            {{ $term->name }}{{ $term->session ? ' - ' . $term->session->name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-xl bg-blue-700 px-5 py-3 text-sm font-black text-white hover:bg-blue-800">
                    Load
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl bg-white shadow">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-black text-gray-900">{{ $selectedClass?->display_name }} Learners</h2>
            <p class="mt-1 text-sm font-semibold text-gray-500">{{ $rankings->count() }} learners</p>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($rankings as $row)
                <div class="grid gap-3 p-4 sm:grid-cols-[90px_1fr_120px_120px] sm:items-center">
                    <div>
                        <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-sm font-black text-blue-700">
                            {{ $row->position ? $row->position . '/' . $row->total_students : 'Pending' }}
                        </span>
                    </div>
                    <div>
                        <p class="font-black text-gray-900">{{ $row->learner->name }}</p>
                        <p class="text-xs font-semibold text-gray-500">{{ $row->learner->registration_number ?: 'No registration number' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-gray-500">Total</p>
                        <p class="font-black text-gray-900">{{ $row->total_score !== null ? number_format($row->total_score, 1) : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-gray-500">Average</p>
                        <p class="font-black text-gray-900">{{ $row->average_score !== null ? number_format($row->average_score, 1) . '%' : '-' }}</p>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">No learners found for this class.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
