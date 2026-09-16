@extends('layouts.app')

@section('title', 'Staff Attendance')

@section('content')
<div class="mx-auto max-w-6xl space-y-5">
    <div class="rounded-2xl bg-white p-5 shadow-xl">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-black text-gray-900">Staff Attendance</h1>
                <p class="text-sm text-gray-500">F-G495 fingerprint and face-recognition records for {{ $date->format('j F Y') }}.</p>
            </div>
            <form method="GET" class="flex gap-2">
                <input type="date" name="date" value="{{ $date->toDateString() }}" class="rounded-xl border border-gray-200 px-3 py-2 text-sm">
                <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white">View</button>
            </form>
        </div>
    </div>
    <div class="overflow-x-auto rounded-2xl bg-white shadow-xl">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-950 text-xs uppercase text-white"><tr><th class="px-4 py-3">Staff</th><th class="px-4 py-3">Machine ID</th><th class="px-4 py-3">Clock in</th><th class="px-4 py-3">Arrival</th><th class="px-4 py-3">Clock out</th><th class="px-4 py-3">Departure</th><th class="px-4 py-3">Action</th></tr></thead>
            <tbody class="divide-y divide-gray-100">
            @foreach($staff as $person)
                @php($record = $person->attendanceRecords->first())
                <tr><td class="px-4 py-3 font-bold">{{ $person->name }}<span class="block text-xs font-normal text-gray-500">{{ ucfirst(str_replace('_', ' ', $person->role)) }}</span></td><td class="px-4 py-3">{{ $person->attendance_machine_user_id ?: '-' }}</td><td class="px-4 py-3">{{ $record?->check_in_at?->format('g:i A') ?: '-' }}</td><td class="px-4 py-3">{{ $record?->arrival_status ? ucfirst(str_replace('_', ' ', $record->arrival_status)) : '-' }}</td><td class="px-4 py-3">{{ $record?->check_out_at?->format('g:i A') ?: '-' }}</td><td class="px-4 py-3">{{ $record?->departure_status ? ucfirst($record->departure_status) : '-' }}</td><td class="px-4 py-3"><details><summary class="cursor-pointer rounded-lg bg-amber-100 px-3 py-2 text-xs font-bold text-amber-900">Edit</summary><form method="POST" action="{{ route('admin.attendance.staff.manual', $person) }}" class="mt-3 min-w-[220px] space-y-2">@csrf<input type="hidden" name="attendance_date" value="{{ $date->toDateString() }}"><label class="block text-[11px] font-bold text-gray-600">Clock in<input type="time" name="check_in" value="{{ $record?->check_in_at?->format('H:i') }}" class="mt-1 w-full rounded-lg border px-2 py-2 text-xs"></label><label class="block text-[11px] font-bold text-gray-600">Clock out<input type="time" name="check_out" value="{{ $record?->check_out_at?->format('H:i') }}" class="mt-1 w-full rounded-lg border px-2 py-2 text-xs"></label><input name="notes" placeholder="Note (optional)" class="w-full rounded-lg border px-2 py-2 text-xs"><button class="w-full rounded-lg bg-amber-500 px-3 py-2 text-xs font-black text-slate-900">Save</button></form></details></td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection