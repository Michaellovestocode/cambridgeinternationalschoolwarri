@extends('layouts.app')

@section('title', 'Teaching Learners')

@section('content')
<div class="space-y-6">
    <div class="rounded-3xl bg-gradient-to-r from-emerald-600 to-cyan-700 p-6 text-white shadow-xl">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-white/70">Read Only</p>
                <h1 class="mt-2 text-3xl font-black">Teaching Learners</h1>
                <p class="mt-2 text-white/85">View learners in the classes assigned to you for teaching.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex rounded-2xl bg-white px-5 py-3 text-sm font-black text-emerald-700 shadow-lg hover:bg-emerald-50">Back to Dashboard</a>
        </div>
    </div>

    <div class="rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm leading-6 text-blue-900">
        This list is read-only for subject teachers. If a learner name is wrongly spelt, please notify the assigned form teacher or admin to correct the official record.
    </div>

    <div class="rounded-3xl bg-white p-5 shadow-lg">
        <form method="GET" action="{{ route('admin.teaching-learners.index') }}" class="flex flex-col gap-3 sm:flex-row">
            <input name="search" value="{{ $search }}" placeholder="Search learner name or registration number" class="min-w-0 flex-1 rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
            <button class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-black text-white hover:bg-slate-800">Search</button>
            @if($search !== '')
                <a href="{{ route('admin.teaching-learners.index') }}" class="rounded-2xl border border-gray-200 px-5 py-3 text-center text-sm font-black text-gray-700 hover:bg-gray-50">Reset</a>
            @endif
        </form>
    </div>

    @forelse($classes as $class)
        <section class="overflow-hidden rounded-3xl bg-white shadow-lg">
            <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-emerald-50 px-6 py-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-black text-gray-900">{{ $class->display_name }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $class->students->count() }} learner{{ $class->students->count() === 1 ? '' : 's' }}</p>
                    </div>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-700">{{ $class->section_label }}</span>
                </div>
            </div>

            @if($class->students->isNotEmpty())
                <div class="grid gap-3 p-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($class->students as $student)
                        <div class="flex items-center gap-3 rounded-2xl border border-gray-100 p-4">
                            @if($student->photo)
                                <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->name }}" class="h-12 w-12 rounded-2xl object-cover">
                            @else
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-sm font-black text-emerald-700">
                                    {{ strtoupper(substr($student->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="truncate font-black text-gray-900">{{ $student->name }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $student->registration_number }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="px-6 py-10 text-center text-sm text-gray-500">
                    No learners found{{ $search !== '' ? ' for this search' : '' }}.
                </div>
            @endif
        </section>
    @empty
        <div class="rounded-3xl bg-white px-6 py-12 text-center shadow-lg">
            <p class="text-lg font-black text-gray-900">No teaching classes assigned yet.</p>
            <p class="mt-2 text-sm text-gray-500">Once admin assigns teaching classes to you, learners will appear here.</p>
        </div>
    @endforelse
</div>
@endsection
