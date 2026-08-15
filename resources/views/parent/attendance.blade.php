@extends('layouts.app')

@section('title', 'Attendance')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-3xl shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black text-gray-900">Attendance</h1>
                <p class="text-sm text-gray-500">Recent check-in / check-out history for your linked child(ren).</p>
            </div>
            <a href="{{ route('parent.dashboard') }}" class="text-sm text-blue-600 hover:underline">← Back to parent dashboard</a>
        </div>
    </div>

    @forelse($children as $child)
        <div class="bg-white rounded-2xl shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold">{{ $child->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $child->class?->display_name ?? 'Unassigned' }}</p>
                </div>
            </div>

            @php
                $list = $records[$child->id] ?? collect();
            @endphp

            @if($list->isEmpty())
                <p class="text-sm text-gray-500">No attendance records found for this child.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Check-in</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Check-out</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Arrival</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Departure</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($list as $rec)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $rec->attendance_date?->format('M j, Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $rec->check_in_at?->format('g:i A') ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $rec->check_out_at?->format('g:i A') ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ ucfirst(str_replace('_',' ', $rec->arrival_status ?? '')) ?: '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ ucfirst(str_replace('_',' ', $rec->departure_status ?? '')) ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @empty
        <div class="bg-white rounded-2xl shadow p-6 text-center text-gray-500">No children linked to this account yet.</div>
    @endforelse

</div>
@endsection
