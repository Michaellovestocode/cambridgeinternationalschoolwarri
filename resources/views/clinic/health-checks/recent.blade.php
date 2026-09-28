@extends('layouts.app')

@section('title', 'Recent Health Checks')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    @if(session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 font-semibold text-emerald-800">{{ session('success') }}</div>@endif
    <header class="overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-800 via-teal-700 to-cyan-800 text-white shadow-xl">
        <div class="flex flex-col gap-5 p-6 sm:p-8 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-emerald-100">School Clinic · Health records</p>
                <h1 class="mt-2 text-3xl font-black sm:text-4xl">{{ auth()->user()->isAdmin() ? 'Health Check Records' : 'Recent Health Checks' }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-emerald-50">Review submitted hostel and term health checks, vital signs, follow-up status, and the staff member who recorded each check.</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                @if(auth()->user()->isNurse())<a href="{{ route('clinic.health-checks.index') }}" class="rounded-xl bg-white px-4 py-3 text-center text-sm font-black text-emerald-900 shadow hover:bg-emerald-50">Record vital signs</a>@endif
                <a href="{{ route('clinic.dashboard') }}" class="rounded-xl border border-white/30 bg-white/10 px-4 py-3 text-center text-sm font-bold text-white hover:bg-white/20">Clinic dashboard</a>
            </div>
        </div>
    </header>

    <section class="grid gap-4 sm:grid-cols-3">
        @foreach([
            ['label' => 'All checks', 'value' => $summary['total'], 'tone' => 'bg-blue-50 text-blue-900'],
            ['label' => 'Checked today', 'value' => $summary['today'], 'tone' => 'bg-emerald-50 text-emerald-900'],
            ['label' => 'Needs follow-up', 'value' => $summary['follow_up'], 'tone' => 'bg-amber-50 text-amber-900'],
        ] as $stat)
            <div class="rounded-2xl {{ $stat['tone'] }} p-5 shadow-sm ring-1 ring-black/5">
                <p class="text-sm font-bold opacity-75">{{ $stat['label'] }}</p>
                <p class="mt-2 text-3xl font-black">{{ number_format($stat['value']) }}</p>
            </div>
        @endforeach
    </section>

    <form method="GET" class="grid gap-3 rounded-2xl bg-white p-4 shadow-sm sm:grid-cols-[minmax(0,1fr)_auto_auto]">
        <label class="sr-only" for="health-check-search">Search records</label>
        <input id="health-check-search" name="search" value="{{ $search }}" class="min-w-0 rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" placeholder="Search student, hostel, check type, or staff">
        <label class="sr-only" for="health-check-period">Date range</label>
        <select id="health-check-period" name="period" class="rounded-xl border border-gray-200 px-4 py-3 text-sm">
            <option value="">All dates</option>
            <option value="today" @selected($period === 'today')>Today</option>
            <option value="week" @selected($period === 'week')>This week</option>
        </select>
        <div class="flex gap-2">
            <button class="flex-1 rounded-xl bg-emerald-700 px-5 py-3 text-sm font-black text-white hover:bg-emerald-800 sm:flex-none">Filter records</button>
            <a href="{{ route('clinic.health-checks.recent') }}" class="rounded-xl bg-gray-100 px-4 py-3 text-center text-sm font-bold text-gray-700">Reset</a>
        </div>
    </form>

    <section class="space-y-3">
        @forelse($checks as $check)
            @php
                $statusTone = match($check->clearance_status) {
                    'referred' => 'bg-rose-100 text-rose-800',
                    'needs_observation' => 'bg-amber-100 text-amber-800',
                    default => 'bg-emerald-100 text-emerald-800',
                };
            @endphp
            <article class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="break-words text-lg font-black text-gray-900">{{ $check->student?->name ?? 'Student record unavailable' }}</h2>
                            <span class="rounded-full px-3 py-1 text-xs font-black {{ $statusTone }}">{{ str_replace('_', ' ', ucfirst($check->clearance_status)) }}</span>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">{{ $check->student?->class?->display_name ?: 'Class unavailable' }} · {{ $check->term_label }} · {{ $check->check_type }}</p>
                        @if($check->hostel_name)<p class="mt-1 text-xs font-semibold text-gray-500">Hostel: {{ $check->hostel_name }}</p>@endif
                    </div>
                    <div class="shrink-0 rounded-xl bg-slate-50 px-4 py-3 text-left lg:text-right">
                        <p class="text-sm font-black text-gray-800">{{ $check->checked_at->format('j M Y') }}</p>
                        <p class="text-xs text-gray-500">{{ $check->checked_at->format('g:i A') }}</p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-5">
                    @foreach([
                        ['Temperature', $check->temperature ? $check->temperature . ' °C' : '—'],
                        ['Pulse', $check->pulse ? $check->pulse . ' bpm' : '—'],
                        ['Weight', $check->weight_kg ? $check->weight_kg . ' kg' : '—'],
                        ['Respiration', $check->respiration ? $check->respiration . ' /min' : '—'],
                        ['Blood pressure', $check->blood_pressure ?: '—'],
                    ] as $reading)
                        <div class="min-w-0 rounded-xl border border-gray-100 bg-white px-3 py-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">{{ $reading[0] }}</p>
                            <p class="mt-1 break-words text-sm font-bold text-gray-800">{{ $reading[1] }}</p>
                        </div>
                    @endforeach
                </div>

                @if($check->remark || $check->health_notes)
                    <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-gray-700">
                        @if($check->remark)<p><span class="font-bold">Remark:</span> {{ $check->remark }}</p>@endif
                        @if($check->health_notes)<p class="{{ $check->remark ? 'mt-1' : '' }} whitespace-pre-line"><span class="font-bold">Health notes:</span> {{ $check->health_notes }}</p>@endif
                    </div>
                @endif
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-xs font-semibold text-gray-400">Recorded by {{ $check->recorder?->name ?? 'Former staff member' }}</p>
                    @if(auth()->user()->isNurse() && $check->recorded_by === auth()->id())
                        <div class="flex gap-2">
                            <a href="{{ route('clinic.health-checks.edit', $check) }}" class="rounded-lg bg-blue-50 px-3 py-2 text-sm font-bold text-blue-700">Edit</a>
                            <form method="POST" action="{{ route('clinic.health-checks.delete', $check) }}" onsubmit="return confirm('Delete this vital-sign record?')">
                                @csrf @method('DELETE')
                                <button class="rounded-lg bg-red-50 px-3 py-2 text-sm font-bold text-red-700">Delete</button>
                            </form>
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center shadow-sm">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-2xl text-emerald-700">＋</div>
                <h2 class="mt-4 text-lg font-black text-gray-900">No health checks found</h2>
                <p class="mt-1 text-sm text-gray-500">Try another search or date range, or record the first check.</p>
                @if(auth()->user()->isNurse())<a href="{{ route('clinic.health-checks.index') }}" class="mt-4 inline-flex rounded-xl bg-emerald-700 px-4 py-3 text-sm font-bold text-white">Record vital signs</a>@endif
            </div>
        @endforelse
    </section>

    <div>{{ $checks->links() }}</div>
</div>
@endsection
