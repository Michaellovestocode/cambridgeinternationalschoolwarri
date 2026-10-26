@extends('layouts.app')

@section('title', 'Student F-G495 Test')

@section('content')
<div class="mx-auto max-w-6xl space-y-5">
    <section class="rounded-2xl bg-slate-950 p-6 text-white shadow-xl">
        <p class="text-xs font-bold uppercase tracking-widest text-emerald-300">Student attendance device test</p>
        <h1 class="mt-2 text-2xl font-black">F-G495 connection test</h1>
        <p class="mt-2 max-w-3xl text-sm text-white/75">This receiver is separate from the staff machine route. It accepts student punches at the ADMS URL below and shows matched and unmatched Enroll IDs here.</p>
    </section>

    <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-950 shadow">
        <h2 class="font-black">Before turning on Real Time Push</h2>
        <ol class="mt-2 list-decimal space-y-1 pl-5">
            <li>Set <code>STUDENT_F_G495_DEVICE_ID</code> on the server to the exact device ID / serial value sent as <code>SN</code> by the reader. The photo shows Device ID <code>00001</code>; confirm the value in the request if it differs.</li>
            <li>Assign each learner's F-G495 Enroll ID in Admin → Attendance → Cards.</li>
            <li>Point the reader to <code>{{ url('/student/iclock/cdata') }}</code>, using its ADMS/HTTP push settings if available, then enable Real Time Push.</li>
        </ol>
        <p class="mt-3">If the machine only accepts a hostname and has no way to set the <code>/student/iclock/cdata</code> path, direct push may not work. Leave the staff machine settings alone while checking compatibility.</p>
        <p class="mt-2 font-semibold">Matched student punches create attendance and queue the existing parent SMS. Unmatched IDs appear below and do not create attendance.</p>
    </section>

    <section class="overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="border-b px-5 py-4">
            <h2 class="font-black text-gray-900">Latest device events</h2>
            <p class="text-xs text-gray-500">Newest 100 events, including unmatched Enroll IDs.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr><th class="px-4 py-3">Time</th><th class="px-4 py-3">Enroll ID</th><th class="px-4 py-3">Student</th><th class="px-4 py-3">Direction</th><th class="px-4 py-3">Result</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($events as $event)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3">{{ $event->punched_at->format('M j, Y g:i:s A') }}</td>
                            <td class="px-4 py-3 font-mono">{{ $event->enroll_id }}</td>
                            <td class="px-4 py-3">{{ $event->user?->name ?? 'Unmatched' }}{{ $event->user?->class ? ' · '.$event->user->class->display_name : '' }}</td>
                            <td class="px-4 py-3">{{ $event->direction ? ucfirst($event->direction) : 'Auto' }}</td>
                            <td class="px-4 py-3">{{ $event->attendanceRecord ? 'Recorded' : ($event->user ? 'Received' : 'Needs ID mapping') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No student device events received yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
