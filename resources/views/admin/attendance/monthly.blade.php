@extends('layouts.app')

@section('title', 'Monthly Attendance')

@section('content')
<div class="mx-auto max-w-6xl space-y-5">
    <div class="rounded-2xl bg-white p-5 shadow-xl">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-black text-gray-900">Monthly Attendance</h1>
                <p class="text-sm text-gray-500">{{ $month->format('F Y') }} - {{ $workingDaysCount }} working days counted</p>
            </div>
            <a href="{{ route('admin.attendance.scanner') }}" class="rounded-xl bg-emerald-600 px-4 py-3 text-center text-sm font-bold text-white">Scanner</a>
            <a href="{{ route('admin.attendance.staff') }}" class="rounded-xl bg-rose-600 px-4 py-3 text-center text-sm font-bold text-white">Staff Attendance</a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.attendance.payroll-export') }}" class="grid gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 md:grid-cols-4 md:items-end">
        <div>
            <label for="payroll_start_date" class="mb-1 block text-xs font-bold text-emerald-900">Payroll start</label>
            <input id="payroll_start_date" type="date" name="start_date" value="{{ $month->copy()->startOfMonth()->toDateString() }}" required class="w-full rounded-xl border border-emerald-200 bg-white px-3 py-3 text-sm">
        </div>
        <div>
            <label for="payroll_end_date" class="mb-1 block text-xs font-bold text-emerald-900">Payroll end</label>
            <input id="payroll_end_date" type="date" name="end_date" value="{{ $month->copy()->endOfMonth()->toDateString() }}" required class="w-full rounded-xl border border-emerald-200 bg-white px-3 py-3 text-sm">
        </div>
        <div>
            <label for="payroll_section" class="mb-1 block text-xs font-bold text-emerald-900">Department</label>
            <select id="payroll_section" name="section" class="w-full rounded-xl border border-emerald-200 bg-white px-3 py-3 text-sm">
                <option value="">All staff</option>
                @foreach($sections as $section => $label)
                    <option value="{{ $section }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button class="rounded-xl bg-emerald-700 px-4 py-3 text-sm font-bold text-white">Export Payroll Excel</button>
    </form>

    <form method="GET" class="grid gap-3 rounded-2xl bg-white p-4 shadow md:grid-cols-5">
        <input type="month" name="month" value="{{ $filters['month'] }}" class="rounded-xl border border-gray-200 px-3 py-3 text-sm">
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
        @forelse($groupedSummaries as $sectionLabel => $sectionSummaries)
            @php
                $sectionPresent = $sectionSummaries->sum('present');
                $sectionLate = $sectionSummaries->sum('late');
                $sectionAbsent = $sectionSummaries->sum('absent');
            @endphp
            <details class="rounded-2xl bg-white shadow" open>
                <summary class="cursor-pointer list-none px-4 py-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="font-black text-gray-900">{{ $sectionLabel }}</p>
                            <p class="text-xs text-gray-500">{{ $sectionSummaries->count() }} people - Present {{ $sectionPresent }} - Late {{ $sectionLate }} - Absent {{ $sectionAbsent }}</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">Open</span>
                    </div>
                </summary>
                <div class="space-y-3 border-t border-gray-100 p-3">
                    @foreach($sectionSummaries as $summary)
                        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-bold text-gray-900">{{ $summary['person']->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $summary['person']->registration_number }} - {{ ucfirst(str_replace('_', ' ', $summary['person']->role)) }}{{ $summary['person']->class ? ' - ' . $summary['person']->class->display_name : '' }}</p>
                                </div>
                                <p class="text-right text-xs text-gray-500">Avg in<br><strong class="text-gray-900">{{ $summary['average_check_in'] ?: '-' }}</strong></p>
                            </div>
                            <div class="mt-3 grid grid-cols-4 gap-2 text-center text-sm">
                                <div class="rounded-xl bg-emerald-50 p-3"><strong class="text-lg text-emerald-700">{{ $summary['present'] }}</strong><br><span class="text-xs">Present</span></div>
                                <div class="rounded-xl bg-rose-50 p-3"><strong class="text-lg text-rose-700">{{ $summary['late'] }}</strong><br><span class="text-xs">Late</span></div>
                                <div class="rounded-xl bg-amber-50 p-3"><strong class="text-lg text-amber-700">{{ $summary['early'] }}</strong><br><span class="text-xs">Early</span></div>
                                <div class="rounded-xl bg-slate-50 p-3"><strong class="text-lg text-slate-700">{{ $summary['absent'] }}</strong><br><span class="text-xs">Absent</span></div>
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
