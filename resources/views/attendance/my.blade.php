@extends('layouts.app')

@section('title', 'My Attendance')

@section('content')
<div class="mx-auto max-w-4xl space-y-5">
    <div class="rounded-2xl bg-white p-5 shadow-xl">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-black text-gray-900">My Attendance</h1>
                <p class="text-sm text-gray-500">{{ $month->format('F Y') }}</p>
            </div>
            <form method="GET">
                <input type="month" name="month" value="{{ $month->format('Y-m') }}" onchange="this.form.submit()" class="rounded-xl border border-gray-200 px-3 py-3 text-sm">
            </form>
        </div>
        <div class="mt-5 grid grid-cols-4 gap-2 text-center">
            <div class="rounded-xl bg-slate-50 p-3"><strong class="text-lg">{{ $workingDaysCount }}</strong><br><span class="text-xs">Days</span></div>
            <div class="rounded-xl bg-emerald-50 p-3"><strong class="text-lg text-emerald-700">{{ $summary['present'] }}</strong><br><span class="text-xs">Present</span></div>
            <div class="rounded-xl bg-rose-50 p-3"><strong class="text-lg text-rose-700">{{ $summary['late'] }}</strong><br><span class="text-xs">Late</span></div>
            <div class="rounded-xl bg-amber-50 p-3"><strong class="text-lg text-amber-700">{{ $summary['early'] }}</strong><br><span class="text-xs">Early</span></div>
        </div>
    </div>

    <div class="space-y-3">
        @forelse($records as $record)
            <div class="rounded-2xl bg-white p-4 shadow">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-bold text-gray-900">{{ $record->attendance_date->format('D, M j') }}</p>
                        <p class="text-sm text-gray-500">In: {{ $record->check_in_at?->format('g:i A') ?: '-' }} | Out: {{ $record->check_out_at?->format('g:i A') ?: '-' }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        @if($record->arrival_status)
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $record->arrival_status === 'late' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">{{ ucfirst(str_replace('_', ' ', $record->arrival_status)) }}</span>
                        @endif
                        @if($record->departure_status)
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $record->departure_status === 'early' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">{{ ucfirst($record->departure_status) }} out</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl bg-white p-6 text-center text-sm text-gray-500 shadow">No attendance record for this month yet.</div>
        @endforelse
    </div>
</div>
@endsection
