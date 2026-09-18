@extends('layouts.app')

@section('title', 'My Clinic Records')

@section('content')
<div class="mx-auto max-w-7xl space-y-5">
    <div class="flex flex-col gap-4 rounded-2xl bg-white p-5 shadow sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide text-blue-700">School Clinic</p>
            <h1 class="mt-1 text-2xl font-black text-gray-900">{{ auth()->user()->isNurse() ? 'My Entered Records' : 'Clinic Records' }}</h1>
            <p class="text-sm text-gray-500">Find earlier visits, vital-sign checks, and incident reports.</p>
        </div>
        <a href="{{ route('clinic.dashboard') }}" class="rounded-xl bg-gray-100 px-4 py-3 text-center text-sm font-bold text-gray-700">← Back to Dashboard</a>
    </div>

    <div class="flex flex-wrap gap-2">
        @foreach(['visits' => 'Clinic Visits', 'health-checks' => 'Hostel Vital Signs', 'incidents' => 'Incident Reports'] as $tab => $label)
            <a href="{{ route('clinic.records.index', ['type' => $tab]) }}" class="rounded-xl px-4 py-2 text-sm font-bold {{ $type === $tab ? 'bg-blue-700 text-white' : 'bg-white text-gray-700 shadow hover:bg-blue-50' }}">{{ $label }}</a>
        @endforeach
    </div>

    <form method="GET" class="grid gap-3 rounded-2xl bg-white p-4 shadow sm:grid-cols-[1fr_auto_auto]">
        <input type="hidden" name="type" value="{{ $type }}">
        <input name="search" value="{{ $search }}" class="min-w-0 rounded-xl border border-gray-200 px-4 py-3" placeholder="Search by student or patient name">
        <select name="period" class="rounded-xl border border-gray-200 px-4 py-3">
            <option value="">All time</option>
            <option value="today" @selected($period === 'today')>Today</option>
            <option value="week" @selected($period === 'week')>This week</option>
            @if($type === 'visits')
                <option value="current" @selected($period === 'current')>Currently in clinic</option>
                <option value="sent-home" @selected($period === 'sent-home')>Sent home today</option>
            @endif
        </select>
        <button class="rounded-xl bg-blue-700 px-5 py-3 font-bold text-white">Filter</button>
    </form>

    <section class="overflow-hidden rounded-2xl bg-white shadow">
        <div class="overflow-x-auto">
            @if($type === 'visits')
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-950 text-xs uppercase text-white"><tr><th class="px-5 py-3">Patient</th><th class="px-5 py-3">Reason</th><th class="px-5 py-3">Outcome</th><th class="px-5 py-3">Date</th><th class="px-5 py-3"></th></tr></thead>
                    <tbody class="divide-y divide-gray-100">@forelse($records as $record)<tr><td class="px-5 py-4 font-bold text-gray-900">{{ $record->display_name }}<span class="block text-xs font-normal text-gray-500">{{ $record->person?->class?->display_name ?: ucfirst($record->patient_type) }}</span></td><td class="px-5 py-4 text-gray-700">{{ $record->reason }}</td><td class="px-5 py-4 text-gray-700">{{ str_replace('_', ' ', ucfirst($record->outcome)) }}</td><td class="px-5 py-4 text-gray-700">{{ $record->visited_at->format('d M Y, g:i A') }}</td><td class="px-5 py-4"><a href="{{ route('clinic.visits.show', $record) }}" class="font-bold text-blue-700 hover:underline">View</a></td></tr>@empty<tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">No clinic visits found.</td></tr>@endforelse</tbody>
                </table>
            @elseif($type === 'health-checks')
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-950 text-xs uppercase text-white"><tr><th class="px-5 py-3">Student</th><th class="px-5 py-3">Vital signs</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Checked</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">@forelse($records as $record)<tr><td class="px-5 py-4 font-bold text-gray-900">{{ $record->student?->name }}<span class="block text-xs font-normal text-gray-500">{{ $record->student?->class?->display_name }}</span></td><td class="px-5 py-4 text-gray-700">{{ $record->temperature ? $record->temperature . ' °C' : '—' }} · {{ $record->pulse ? $record->pulse . ' bpm' : '—' }} · {{ $record->weight_kg ? $record->weight_kg . ' kg' : '—' }}</td><td class="px-5 py-4 text-gray-700">{{ str_replace('_', ' ', ucfirst($record->clearance_status)) }}</td><td class="px-5 py-4 text-gray-700">{{ $record->checked_at->format('d M Y, g:i A') }}</td></tr>@empty<tr><td colspan="4" class="px-5 py-10 text-center text-gray-500">No vital-sign checks found.</td></tr>@endforelse</tbody>
                </table>
            @else
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-950 text-xs uppercase text-white"><tr><th class="px-5 py-3">Student</th><th class="px-5 py-3">Incident</th><th class="px-5 py-3">Location</th><th class="px-5 py-3">Date</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">@forelse($records as $record)<tr><td class="px-5 py-4 font-bold text-gray-900">{{ $record->student?->name }}<span class="block text-xs font-normal text-gray-500">{{ $record->student?->class?->display_name }}</span></td><td class="px-5 py-4 text-gray-700">{{ $record->incident_type }}</td><td class="px-5 py-4 text-gray-700">{{ $record->location ?: '—' }}</td><td class="px-5 py-4 text-gray-700">{{ $record->incident_at->format('d M Y, g:i A') }}</td></tr>@empty<tr><td colspan="4" class="px-5 py-10 text-center text-gray-500">No incident reports found.</td></tr>@endforelse</tbody>
                </table>
            @endif
        </div>
    </section>

    {{ $records->links() }}
</div>
@endsection
