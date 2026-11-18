@extends('layouts.app')
@section('title', 'Learner Attendance Check')
@section('content')
<main class="mx-auto max-w-7xl space-y-5">
    <header class="rounded-2xl bg-gradient-to-r from-slate-900 via-blue-900 to-cyan-800 p-5 text-white shadow-xl sm:p-7">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div><p class="text-xs font-black uppercase tracking-widest text-cyan-200">Daily follow-up</p><h1 class="mt-2 text-2xl font-black sm:text-3xl">Learner attendance</h1><p class="mt-1 text-sm text-white/75">Check who has not clocked in or still needs to clock out.</p></div>
            <a href="{{ route('admin.attendance.today') }}" class="rounded-xl bg-white/15 px-4 py-3 text-center text-sm font-bold text-white hover:bg-white/25">All attendance</a>
        </div>
        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach(['total' => ['Learners', 'text-white'], 'not_clocked_in' => ['Not clocked in', 'text-rose-200'], 'not_clocked_out' => ['Missing clock-out', 'text-amber-200'], 'complete' => ['Clocking complete', 'text-emerald-200']] as $key => [$label, $color])
                <div class="rounded-xl bg-white/10 p-3"><p class="text-xs font-semibold text-white/70">{{ $label }}</p><p class="mt-1 text-2xl font-black {{ $color }}">{{ $stats[$key] }}</p></div>
            @endforeach
        </div>
    </header>

    <form method="GET" class="grid gap-3 rounded-2xl bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-5 lg:items-end">
        <label class="text-xs font-bold text-gray-600">Date<input type="date" name="date" value="{{ $filters['date'] }}" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-3 text-sm"></label>
        <label class="text-xs font-bold text-gray-600">Class<select name="class_id" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-3 text-sm"><option value="">All classes</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected($filters['class_id'] == $class->id)>{{ $class->display_name }}</option>@endforeach</select></label>
        <label class="text-xs font-bold text-gray-600">Clocking status<select name="status" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-3 text-sm"><option value="">All learners</option><option value="not_clocked_in" @selected($filters['status'] === 'not_clocked_in')>Not clocked in</option><option value="not_clocked_out" @selected($filters['status'] === 'not_clocked_out')>Missing clock-out</option><option value="complete" @selected($filters['status'] === 'complete')>Complete</option></select></label>
        <label class="text-xs font-bold text-gray-600">Search learner<input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Name or admission number" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-3 text-sm"></label>
        <button class="rounded-xl bg-blue-700 px-4 py-3 text-sm font-bold text-white hover:bg-blue-800">Apply filters</button>
    </form>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($students as $student)
            @php($record = $student->today_attendance)
            <article class="min-w-0 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0"><h2 class="break-words font-black text-gray-900">{{ $student->name }}</h2><p class="mt-1 text-xs text-gray-500">{{ $student->registration_number ?: 'No admission number' }} · {{ $student->class?->display_name ?? 'No class' }}</p></div>
                    @if($student->today_attendance_status === 'not_clocked_in')<span class="shrink-0 rounded-full bg-rose-100 px-2.5 py-1 text-[10px] font-black uppercase text-rose-800">Not clocked in</span>@elseif($student->today_attendance_status === 'not_clocked_out')<span class="shrink-0 rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-black uppercase text-amber-800">Missing clock-out</span>@else<span class="shrink-0 rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-black uppercase text-emerald-800">Complete</span>@endif
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2 text-sm"><div class="rounded-xl bg-gray-50 p-3"><p class="text-xs text-gray-500">Clock in</p><p class="mt-1 font-bold text-gray-900">{{ $record?->check_in_at?->format('g:i A') ?? '—' }}</p></div><div class="rounded-xl bg-gray-50 p-3"><p class="text-xs text-gray-500">Clock out</p><p class="mt-1 font-bold text-gray-900">{{ $record?->check_out_at?->format('g:i A') ?? '—' }}</p></div></div>
            </article>
        @empty
            <div class="rounded-2xl bg-white p-8 text-center text-sm text-gray-500 shadow-sm sm:col-span-2 xl:col-span-3">No learners match the selected filters.</div>
        @endforelse
    </section>
</main>
@endsection
