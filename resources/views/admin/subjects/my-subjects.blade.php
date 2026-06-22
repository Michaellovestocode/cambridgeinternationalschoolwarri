@extends('layouts.app')

@section('title', 'My Assigned Subjects')

@section('content')
@php
    $totalClasses = $subjects->flatMap(fn ($subject) => $subject->assignedClasses->pluck('id'))->unique()->count();
@endphp

<div class="space-y-6">
    <div class="rounded-2xl bg-gradient-to-r from-indigo-600 via-blue-600 to-cyan-600 p-6 text-white shadow-xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase text-white/75" style="letter-spacing:.14em;">Teaching Load</p>
                <h1 class="mt-2 text-2xl font-black sm:text-3xl">My Assigned Subjects</h1>
                <p class="mt-1 text-sm text-blue-50">Subjects you teach and the classes attached to each subject.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center rounded-xl bg-white px-5 py-3 text-sm font-black text-blue-700 shadow hover:bg-blue-50">
                Back to Dashboard
            </a>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow">
            <p class="text-sm font-bold text-gray-500">Assigned Subjects</p>
            <p class="mt-2 text-3xl font-black text-blue-700">{{ $subjects->count() }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow">
            <p class="text-sm font-bold text-gray-500">Teaching Classes</p>
            <p class="mt-2 text-3xl font-black text-emerald-700">{{ $totalClasses }}</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow">
            <p class="text-sm font-bold text-gray-500">Created Exams</p>
            <p class="mt-2 text-3xl font-black text-amber-700">{{ $subjects->sum('exams_count') }}</p>
        </div>
    </div>

    @if(($ownedEarlyPrimaryClasses ?? collect())->isNotEmpty())
        <div class="rounded-2xl border border-pink-100 bg-pink-50 p-5 text-pink-900">
            <h2 class="text-base font-black">Form Teacher Access</h2>
            <p class="mt-1 text-sm font-semibold text-pink-800">
                Your Early Years and Primary form-teacher classes are shown here. In those sections, class subjects are included for your learners.
            </p>
        </div>
    @endif

    <div class="rounded-2xl bg-white shadow-lg">
        <div class="border-b border-gray-100 px-5 py-4 sm:px-6">
            <h2 class="text-xl font-black text-gray-900">Subject And Class List</h2>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($subjects as $subject)
                <div class="p-5 sm:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-lg font-black text-gray-900">{{ $subject->name }}</h3>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                @if($subject->code)
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">{{ $subject->code }}</span>
                                @endif
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700">{{ $subject->assignedClasses->count() }} classes</span>
                                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">{{ $subject->exams_count }} exams</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @forelse($subject->assignedClasses as $class)
                            <span class="rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-sm font-bold text-emerald-800">
                                {{ $class->display_name }}
                                <span class="ml-1 text-xs font-semibold text-emerald-600">({{ $class->students_count ?? 0 }} learners)</span>
                            </span>
                        @empty
                            <span class="rounded-xl border border-rose-100 bg-rose-50 px-3 py-2 text-sm font-bold text-rose-700">
                                No class assigned for this subject yet
                            </span>
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <h3 class="text-lg font-black text-gray-900">No subjects assigned yet</h3>
                    <p class="mt-2 text-sm text-gray-500">When the admin assigns subjects to you, they will appear here.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
