@extends('layouts.app')

@section('title', 'Today Attendance')

@section('content')
<div class="mx-auto max-w-6xl space-y-5">
    <div class="rounded-2xl bg-white p-5 shadow-xl">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-black text-gray-900">Daily Attendance</h1>
                <p class="text-sm text-gray-500">{{ $date->format('l, F j, Y') }}</p>
            </div>
            <a href="{{ route('admin.attendance.scanner') }}" class="rounded-xl bg-emerald-600 px-4 py-3 text-center text-sm font-bold text-white">Open Scanner</a>
        </div>
        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-5">
            @foreach(['expected' => 'Expected', 'present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'early' => 'Early Out'] as $key => $label)
                <div class="rounded-xl bg-slate-50 p-3 text-center">
                    <p class="text-2xl font-black text-gray-900">{{ $stats[$key] }}</p>
                    <p class="text-xs font-semibold text-gray-500">{{ $label }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <form method="GET" class="grid gap-3 rounded-2xl bg-white p-4 shadow md:grid-cols-5">
        <input type="date" name="date" value="{{ $filters['date'] }}" class="rounded-xl border border-gray-200 px-3 py-3 text-sm">
        <select name="role" class="rounded-xl border border-gray-200 px-3 py-3 text-sm">
            <option value="">All groups</option>
            @foreach(['student' => 'Students', 'teacher' => 'Teachers', 'non_teaching_staff' => 'Non-teaching staff', 'admin' => 'Admin'] as $role => $label)
                <option value="{{ $role }}" @selected($filters['role'] === $role)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="section" class="rounded-xl border border-gray-200 px-3 py-3 text-sm">
            <option value="">All sections</option>
            @foreach($sections as $section => $label)
                <option value="{{ $section }}" @selected($filters['section'] === $section)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="class_id" class="rounded-xl border border-gray-200 px-3 py-3 text-sm">
            <option value="">All classes</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected($filters['class_id'] == $class->id)>{{ $class->display_name }}</option>
            @endforeach
        </select>
        <button class="rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white">Filter</button>
    </form>

    <div class="space-y-4">
        @forelse($groupedPeople as $sectionLabel => $sectionPeople)
            <details class="rounded-2xl bg-white shadow" open>
                <summary class="cursor-pointer list-none px-4 py-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="font-black text-gray-900">{{ $sectionLabel }}</p>
                            <p class="text-xs text-gray-500">{{ $sectionPeople->count() }} people</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">Open</span>
                    </div>
                </summary>
                <div class="space-y-3 border-t border-gray-100 p-3">
                    @foreach($sectionPeople as $person)
                        @php($record = $records->get($person->id))
                        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-bold text-gray-900">{{ $person->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $person->registration_number }} - {{ ucfirst(str_replace('_', ' ', $person->role)) }}{{ $person->class ? ' - ' . $person->class->display_name : '' }}</p>
                                </div>
                                @if($record?->check_in_at)
                                    <span class="shrink-0 rounded-full px-3 py-1 text-xs font-bold {{ $record->arrival_status === 'late' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                        {{ ucfirst(str_replace('_', ' ', $record->arrival_status)) }}
                                    </span>
                                @else
                                    <span class="shrink-0 rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-600">Absent</span>
                                @endif
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                                <div class="rounded-xl bg-white p-3">In<br><strong>{{ $record?->check_in_at?->format('g:i A') ?: '-' }}</strong></div>
                                <div class="rounded-xl bg-white p-3">Out<br><strong>{{ $record?->check_out_at?->format('g:i A') ?: '-' }}</strong></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </details>
        @empty
            <div class="rounded-2xl bg-white p-6 text-center text-sm text-gray-500 shadow">No people found for this filter.</div>
        @endforelse
    </div>
</div>
@endsection
