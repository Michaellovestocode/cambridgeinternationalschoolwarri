@extends('layouts.app')

@section('title', 'Class Score Entry')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl bg-gradient-to-r from-pink-600 via-indigo-600 to-blue-700 p-6 text-white shadow-xl">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-bold uppercase text-white/75" style="letter-spacing:.14em;">Class Teacher Score Entry</p>
                <h1 class="mt-2 text-2xl font-black sm:text-3xl">Fill Learner Scores</h1>
                <p class="mt-1 text-sm text-blue-50">Choose a learner, then fill all subject scores on one page.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center rounded-xl bg-white px-5 py-3 text-sm font-black text-blue-700 shadow hover:bg-blue-50">
                Back to Dashboard
            </a>
        </div>
    </div>

    <div class="rounded-2xl bg-white p-5 shadow">
        <form method="GET" action="{{ route('admin.report-cards.class-score-entry') }}" class="grid gap-4 lg:grid-cols-4">
            <div>
                <label for="class_id" class="mb-2 block text-sm font-bold text-gray-700">Class</label>
                <select id="class_id" name="class_id" class="w-full rounded-xl border border-gray-300 px-4 py-3">
                    @foreach($formClasses as $class)
                        <option value="{{ $class->id }}" {{ (int) $selectedClass?->id === (int) $class->id ? 'selected' : '' }}>
                            {{ $class->display_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="session_id" class="mb-2 block text-sm font-bold text-gray-700">Session</label>
                <select id="session_id" name="session_id" class="w-full rounded-xl border border-gray-300 px-4 py-3">
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}" {{ (int) $selectedSession?->id === (int) $session->id ? 'selected' : '' }}>
                            {{ $session->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="term_id" class="mb-2 block text-sm font-bold text-gray-700">Term</label>
                <select id="term_id" name="term_id" class="w-full rounded-xl border border-gray-300 px-4 py-3">
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}" {{ (int) $selectedTerm?->id === (int) $term->id ? 'selected' : '' }}>
                            {{ $term->name }}{{ $term->session ? ' - ' . $term->session->name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-xl bg-blue-700 px-5 py-3 text-sm font-black text-white hover:bg-blue-800">
                    Load Learners
                </button>
            </div>
        </form>
    </div>

    <div class="rounded-2xl bg-white shadow-lg">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-xl font-black text-gray-900">{{ $selectedClass?->display_name }} Learners</h2>
            <p class="mt-1 text-sm font-semibold text-gray-500">{{ $learners->count() }} learners found</p>
        </div>

        <div class="grid gap-3 p-5 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($learners as $learner)
                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                    <div class="flex items-center gap-3">
                        @if($learner->photo)
                            <img src="{{ asset('storage/' . $learner->photo) }}" alt="{{ $learner->name }}" class="h-14 w-14 rounded-2xl object-cover">
                        @else
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-blue-100 text-lg font-black text-blue-700">
                                {{ strtoupper(substr($learner->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <h3 class="break-words text-base font-black text-gray-900">{{ $learner->name }}</h3>
                            <p class="text-xs font-semibold text-gray-500">{{ $learner->registration_number ?: 'No registration number' }}</p>
                        </div>
                    </div>

                    <a href="{{ route('admin.report-cards.manual', [
                        'session_id' => $selectedSession?->id,
                        'term_id' => $selectedTerm?->id,
                        'class_id' => $selectedClass?->id,
                        'student_id' => $learner->id,
                    ]) }}" class="mt-4 flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-black text-white hover:bg-emerald-700">
                        Fill Scores
                    </a>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-gray-200 p-8 text-center">
                    <h3 class="text-lg font-black text-gray-900">No learners found</h3>
                    <p class="mt-2 text-sm text-gray-500">No learner is currently assigned to this class.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
