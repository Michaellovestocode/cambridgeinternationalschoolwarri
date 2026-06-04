@extends('layouts.app')

@section('title', 'Attendance Scanner')

@section('content')
<div class="mx-auto max-w-5xl space-y-5">
    <div class="rounded-2xl bg-slate-950 p-5 text-white shadow-xl sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-300">School Attendance</p>
                <h1 class="mt-2 text-2xl font-black sm:text-3xl">Scanner</h1>
                <p class="mt-1 text-sm text-white/70">Resumption: {{ \Carbon\Carbon::createFromFormat('H:i:s', $resumptionTime)->format('g:i A') }}. Closing: {{ \Carbon\Carbon::createFromFormat('H:i:s', $closingTime)->format('g:i A') }}.</p>
            </div>
            <div class="grid grid-cols-2 gap-2 text-center sm:grid-cols-4">
                <div class="rounded-xl bg-white/10 px-3 py-2">
                    <p class="text-lg font-black">{{ $stats['present'] }}</p>
                    <p class="text-[11px] text-white/60">Present</p>
                </div>
                <div class="rounded-xl bg-white/10 px-3 py-2">
                    <p class="text-lg font-black">{{ $stats['late'] }}</p>
                    <p class="text-[11px] text-white/60">Late</p>
                </div>
                <div class="rounded-xl bg-white/10 px-3 py-2">
                    <p class="text-lg font-black">{{ $stats['early'] }}</p>
                    <p class="text-[11px] text-white/60">Early Out</p>
                </div>
                <div class="rounded-xl bg-white/10 px-3 py-2">
                    <p class="text-lg font-black">{{ $stats['absent'] }}</p>
                    <p class="text-[11px] text-white/60">Absent</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-[1fr,0.85fr]">
        <section class="rounded-2xl bg-white p-5 shadow-xl sm:p-6">
            <form id="scanner-form" class="space-y-4">
                <div>
                    <label for="card_uid" class="block text-sm font-bold text-gray-800">Scan card</label>
                    <input id="card_uid" name="card_uid" autocomplete="off" autofocus
                        class="mt-2 w-full rounded-2xl border-2 border-emerald-200 bg-emerald-50 px-4 py-5 text-center text-xl font-black tracking-wide text-gray-900 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                        placeholder="Tap scanner here">
                    <p class="mt-2 text-xs text-gray-500">Most scanners type the card value and press Enter automatically.</p>
                </div>
                <button type="submit" class="w-full rounded-2xl bg-emerald-600 px-5 py-4 font-bold text-white shadow-lg">Record Scan</button>
            </form>

            <div id="scan-result" class="mt-5 hidden rounded-2xl border p-5"></div>

            <div class="mt-5 grid grid-cols-2 gap-3">
                <a href="{{ route('admin.attendance.today') }}" class="rounded-2xl bg-blue-50 px-4 py-4 text-center text-sm font-bold text-blue-700">Today</a>
                <a href="{{ route('admin.attendance.monthly') }}" class="rounded-2xl bg-violet-50 px-4 py-4 text-center text-sm font-bold text-violet-700">Monthly</a>
                <a href="{{ route('admin.attendance.people') }}" class="rounded-2xl bg-amber-50 px-4 py-4 text-center text-sm font-bold text-amber-700">Cards</a>
                <a href="{{ route('attendance.my') }}" class="rounded-2xl bg-slate-100 px-4 py-4 text-center text-sm font-bold text-slate-700">My Log</a>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-5 shadow-xl sm:p-6">
            <h2 class="text-lg font-black text-gray-900">Recent scans</h2>
            <div class="mt-4 space-y-3">
                @forelse($records as $record)
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-bold text-gray-900">{{ $record->user->name }}</p>
                                <p class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $record->user->role)) }}{{ $record->user->class ? ' • ' . $record->user->class->display_name : '' }}</p>
                            </div>
                            <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $record->arrival_status === 'late' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $record->arrival_status ? ucfirst(str_replace('_', ' ', $record->arrival_status)) : 'No in' }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">In: {{ $record->check_in_at?->format('g:i A') ?: '-' }} | Out: {{ $record->check_out_at?->format('g:i A') ?: '-' }}</p>
                    </div>
                @empty
                    <p class="rounded-xl bg-gray-50 p-4 text-sm text-gray-500">No scans yet today.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>

<script>
const form = document.getElementById('scanner-form');
const input = document.getElementById('card_uid');
const result = document.getElementById('scan-result');

function showResult(data, ok = true) {
    result.className = `mt-5 rounded-2xl border p-5 ${ok ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-rose-200 bg-rose-50 text-rose-900'}`;
    result.innerHTML = ok
        ? `<p class="text-sm font-bold">${data.message}</p><p class="mt-2 text-2xl font-black">${data.person.name}</p><p class="text-sm">${data.person.role}${data.person.class ? ' • ' + data.person.class : ''}</p><p class="mt-3 text-sm">In: <strong>${data.record.check_in || '-'}</strong> | Out: <strong>${data.record.check_out || '-'}</strong></p>`
        : `<p class="text-sm font-bold">${data.message || 'Scan failed.'}</p>`;
    result.classList.remove('hidden');
}

form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const cardUid = input.value.trim();
    if (!cardUid) return;

    try {
        const response = await fetch('{{ route('admin.attendance.scan') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ card_uid: cardUid }),
        });
        const data = await response.json();
        showResult(data, response.ok && data.ok);
    } catch (error) {
        showResult({ message: 'Network error. Please try again.' }, false);
    }

    input.value = '';
    input.focus();
});

window.addEventListener('load', () => input.focus());
document.addEventListener('click', () => input.focus());
</script>
@endsection
