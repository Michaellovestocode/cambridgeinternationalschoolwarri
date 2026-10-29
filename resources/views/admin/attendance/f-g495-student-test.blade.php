@extends('layouts.app')

@section('title', 'Student Clock Dashboard')

@section('content')
<div class="mx-auto max-w-6xl space-y-5">
    <section class="rounded-2xl bg-slate-950 p-6 text-white shadow-xl">
        <p class="text-xs font-bold uppercase tracking-widest text-emerald-300">Student attendance</p>
        <div class="mt-2 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-black">Student Clock Dashboard</h1>
                <p class="mt-2 max-w-3xl text-sm text-white/75">See which learner punches reached the website, whether each Enroll ID is matched, and the clock-in and clock-out times recorded for that day.</p>
            </div>
            <a href="{{ route('admin.attendance.people') }}" class="rounded-xl bg-emerald-500 px-4 py-3 text-center text-sm font-bold text-slate-950 hover:bg-emerald-400">Map learner Enroll IDs</a>
        </div>
    </section>
    <section class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Current receiver ID</p>
            <p class="mt-2 font-mono text-2xl font-black text-slate-900">{{ implode(', ', config('services.student_attendance.device_ids', [config('services.student_attendance.device_id')])) ?: 'Not configured' }}</p>
            <p class="mt-1 text-xs text-slate-500">Receiver IDs accepted by the deployed website.</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Latest punch time</p>
            <p class="mt-2 text-lg font-black text-slate-900">{{ $events->first()?->punched_at?->format('M j, Y g:i A') ?? 'No punches yet' }}</p>
            <p class="mt-1 text-xs text-slate-500">Clock time from the latest event.</p>
        </div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow">
            <p class="text-xs font-bold uppercase tracking-wide text-amber-800">Unmatched in latest 100</p>
            <p class="mt-2 text-2xl font-black text-amber-950">{{ $events->whereNull('user_id')->count() }}</p>
            <p class="mt-1 text-xs text-amber-800">Map those Enroll IDs to learners so attendance can be recorded.</p>
        </div>
    </section>
    <section class="rounded-2xl border border-blue-200 bg-blue-50 p-5 text-sm text-blue-950 shadow">
        <h2 class="font-black">How the student setup works</h2>
        <ol class="mt-2 list-decimal space-y-1 pl-5">
            <li>One student connector can poll all three clocks when each clock and the laptop are on the same local network.</li>
            <li>It reads stored punches and uploads them to this website. Punches already read by the connector are saved separately per clock and retried if internet access drops.</li>
            <li>Keep the laptop on and connected, leave the student connector running, and keep every <code>student-connector-state*.json</code> file in the connector folder.</li>
        </ol>
        <p class="mt-3 font-semibold">The receiver IDs are <code>00002</code> (Years 4-6), <code>00003</code> (Years 7-9), and <code>00004</code> (Years 10-12), once those IDs are enabled in the deployed website configuration.</p>
        <p class="mt-2">Matched punches update learner attendance and queue a guardian SMS. The learner's Parent/Guardian phone is used first; a linked parent account is a fallback if that field is blank. Unmatched Enroll IDs do not create attendance.</p>
    </section>
    <section class="overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="border-b px-5 py-4">
            <h2 class="font-black text-gray-900">Latest student punches</h2>
            <p class="text-xs text-gray-500">Newest 100 punches by clock time. Clock-in/out values come from that learner's daily attendance record.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr><th class="px-4 py-3">Punch time</th><th class="px-4 py-3">Receiver SN</th><th class="px-4 py-3">Enroll ID</th><th class="px-4 py-3">Learner</th><th class="px-4 py-3">Clock in</th><th class="px-4 py-3">Clock out</th><th class="px-4 py-3">Punch</th><th class="px-4 py-3">Result</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($events as $event)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3">{{ $event->punched_at->format('M j, Y g:i:s A') }}</td>
                            <td class="px-4 py-3 font-mono">{{ $event->device_id }}</td>
                            <td class="px-4 py-3 font-mono">{{ $event->enroll_id }}</td>
                            <td class="px-4 py-3">{{ $event->user?->name ?? 'Unmatched' }}{{ $event->user?->class ? ' · '.$event->user->class->display_name : '' }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $event->attendanceRecord?->check_in_at?->format('g:i A') ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $event->attendanceRecord?->check_out_at?->format('g:i A') ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $event->direction ? ucfirst($event->direction) : 'Auto' }}</td>
                            <td class="px-4 py-3">
                                @if($event->attendanceRecord)
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800">Recorded</span>
                                @elseif($event->user)
                                    <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-800">Matched</span>
                                @else
                                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800">Needs ID mapping</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">No student punches have reached the website yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection