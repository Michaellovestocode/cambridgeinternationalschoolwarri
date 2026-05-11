@extends('layouts.app')

@section('title', 'Fee Clearances')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">School Fee Clearances</h1>
                <p class="text-sm text-gray-500 mt-1">Approve report-card access per student, session, and term.</p>
            </div>
            <a href="{{ route('admin.report-cards') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold">
                Report Cards
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow p-6">
        <form method="GET" action="{{ route('admin.fee-clearances.index') }}" class="grid lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Session</label>
                <select name="session_id" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}" {{ $selectedSessionId === $session->id ? 'selected' : '' }}>
                            {{ $session->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Term</label>
                <select name="term_id" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}" {{ $selectedTermId === $term->id ? 'selected' : '' }}>
                            {{ $term->session?->name }} - {{ $term->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Class</label>
                <select name="class_id" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    <option value="">All classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->display_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Name or reg no">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-lg font-semibold">
                    Filter
                </button>
                <a href="{{ route('admin.fee-clearances.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-5 py-3 rounded-lg font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-600">
                        <th class="px-4 py-3 text-left">Student</th>
                        <th class="px-4 py-3 text-left">Class</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Payment Details</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        @php
                            $clearance = $clearances[$student->id] ?? null;
                            $isApproved = (bool) ($clearance?->is_approved);
                        @endphp
                        <tr class="border-t border-gray-100 align-top">
                            <td class="px-4 py-4">
                                <p class="font-semibold text-gray-900">{{ $student->name }}</p>
                                <p class="text-xs text-gray-500">{{ $student->registration_number }}</p>
                            </td>
                            <td class="px-4 py-4">{{ $student->class?->display_name ?? 'No class' }}</td>
                            <td class="px-4 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $isApproved ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $isApproved ? 'Approved' : 'Locked' }}
                                </span>
                                @if($clearance?->approved_at)
                                    <p class="text-xs text-gray-500 mt-2">Approved {{ $clearance->approved_at->format('M j, Y') }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <form id="fee-clearance-{{ $student->id }}" method="POST" action="{{ route('admin.fee-clearances.update', $student) }}" class="space-y-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="session_id" value="{{ $selectedSessionId }}">
                                    <input type="hidden" name="term_id" value="{{ $selectedTermId }}">
                                    <input type="hidden" name="is_approved" value="{{ $isApproved ? 0 : 1 }}">
                                    <div class="grid md:grid-cols-2 gap-2">
                                        <input type="number" step="0.01" min="0" name="amount_paid" value="{{ old('amount_paid', $clearance?->amount_paid) }}" class="border border-gray-300 rounded-lg px-3 py-2" placeholder="Amount paid">
                                        <input type="text" name="payment_reference" value="{{ old('payment_reference', $clearance?->payment_reference) }}" class="border border-gray-300 rounded-lg px-3 py-2" placeholder="Payment reference">
                                    </div>
                                    <textarea name="note" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Optional note">{{ old('note', $clearance?->note) }}</textarea>
                                </form>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <button type="submit" form="fee-clearance-{{ $student->id }}"
                                        class="{{ $isApproved ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white px-4 py-2 rounded-lg font-semibold">
                                    {{ $isApproved ? 'Revoke' : 'Approve' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-500">No students found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-6">
            {{ $students->links() }}
        </div>
    </div>
</div>
@endsection
