@extends('layouts.app')

@section('title', 'Staff Attendance Details')

@section('content')
<div class="mx-auto max-w-6xl space-y-5">
    <div class="rounded-2xl bg-white p-5 shadow-xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-wide text-emerald-700">Staff Attendance Details</p>
                <h1 class="mt-1 text-2xl font-black text-gray-900">{{ $user->name }}</h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ ucfirst(str_replace('_', ' ', $user->role)) }} · {{ $startDate->format('j M Y') }} to {{ $endDate->format('j M Y') }}
                </p>
            </div>
            <a href="{{ route('admin.attendance.monthly', ['month' => $startDate->format('Y-m')]) }}" class="rounded-xl bg-slate-900 px-4 py-3 text-center text-sm font-bold text-white">Back to Monthly</a>
        </div>
    </div>

    <form method="GET" class="grid gap-3 rounded-2xl bg-white p-4 shadow md:grid-cols-3 md:items-end">
        <div>
            <label for="start_date" class="mb-1 block text-xs font-bold text-gray-700">Start date</label>
            <input id="start_date" type="date" name="start_date" value="{{ $startDate->toDateString() }}" required class="w-full rounded-xl border border-gray-200 px-3 py-3 text-sm">
        </div>
        <div>
            <label for="end_date" class="mb-1 block text-xs font-bold text-gray-700">End date</label>
            <input id="end_date" type="date" name="end_date" value="{{ $endDate->toDateString() }}" required class="w-full rounded-xl border border-gray-200 px-3 py-3 text-sm">
        </div>
        <button class="rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white">View Period</button>
    </form>

    <div class="overflow-x-auto rounded-2xl bg-white shadow-xl">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-950 text-xs uppercase text-white">
                <tr>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Clock in</th>
                    <th class="px-4 py-3">Arrival</th>
                    <th class="px-4 py-3">Clock out</th>
                    <th class="px-4 py-3">Departure</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($dates as $date)
                    @php($record = $records->get($date->toDateString()))
                    <tr>
                        <td class="whitespace-nowrap px-4 py-3 font-bold text-gray-900">{{ $date->format('D, j M Y') }}</td>
                        <td class="whitespace-nowrap px-4 py-3">{{ $record?->check_in_at?->format('g:i A') ?: '-' }}</td>
                        <td class="px-4 py-3">{{ $record?->arrival_status ? ucfirst(str_replace('_', ' ', $record->arrival_status)) : '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3">{{ $record?->check_out_at?->format('g:i A') ?: '-' }}</td>
                        <td class="px-4 py-3">{{ $record?->departure_status ? ucfirst(str_replace('_', ' ', $record->departure_status)) : '-' }}</td>
                        <td class="px-4 py-3">
                            @if(!$record || !$record->check_in_at)
                                <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-800">Absent</span>
                            @elseif(!$record->check_out_at)
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">Missing clock-out</span>
                            @else
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">Complete</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
