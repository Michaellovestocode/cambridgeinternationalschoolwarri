@extends('layouts.app')

@section('title', 'Admissions Inbox')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900">Admissions Inbox</h1>
            <p class="text-sm text-gray-500">Review website enquiries and full admission applications in one place.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-blue-600 hover:underline">Back to dashboard</a>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        @php
            $summaryCards = [
                ['label' => 'New', 'value' => $summary[\App\Models\AdmissionEnquiry::STATUS_NEW] ?? 0, 'class' => 'from-sky-500 to-blue-600'],
                ['label' => 'Under Review', 'value' => $summary[\App\Models\AdmissionEnquiry::STATUS_UNDER_REVIEW] ?? 0, 'class' => 'from-amber-400 to-orange-500'],
                ['label' => 'Approved', 'value' => $summary[\App\Models\AdmissionEnquiry::STATUS_APPROVED] ?? 0, 'class' => 'from-emerald-500 to-green-600'],
                ['label' => 'Rejected', 'value' => $summary[\App\Models\AdmissionEnquiry::STATUS_REJECTED] ?? 0, 'class' => 'from-rose-500 to-red-600'],
                ['label' => 'Contacted', 'value' => $summary[\App\Models\AdmissionEnquiry::STATUS_CONTACTED] ?? 0, 'class' => 'from-violet-500 to-indigo-600'],
            ];
        @endphp

        @foreach($summaryCards as $card)
            <div class="rounded-2xl bg-gradient-to-br {{ $card['class'] }} p-5 text-white shadow-lg">
                <p class="text-xs font-semibold uppercase tracking-wide text-white/80">{{ $card['label'] }}</p>
                <p class="mt-3 text-3xl font-black">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="bg-white shadow rounded-2xl p-6">
        <form method="GET" action="{{ route('admin.enquiries.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="search" class="block text-xs font-semibold text-gray-500 mb-1">Search</label>
                <input id="search" name="search" type="text" value="{{ $filters['search'] }}"
                    placeholder="Parent, email, phone, student" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="status" class="block text-xs font-semibold text-gray-500 mb-1">Status</label>
                <select name="status" id="status"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">All statuses</option>
                    @foreach($statuses as $status)
                    <option value="{{ $status }}" {{ $filters['status'] === $status ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-3">
                <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 rounded-xl font-semibold hover:bg-blue-700 transition">Filter</button>
                <a href="{{ route('admin.enquiries.index') }}"
                    class="w-full border border-gray-200 text-gray-700 py-2 rounded-xl text-center">Reset</a>
            </div>
        </form>
    </div>

    <div class="bg-white shadow rounded-2xl overflow-hidden">
        <div class="w-full overflow-x-auto">
            @php
                $statusClasses = [
                    \App\Models\AdmissionEnquiry::STATUS_NEW => 'bg-sky-100 text-sky-700',
                    \App\Models\AdmissionEnquiry::STATUS_UNDER_REVIEW => 'bg-amber-100 text-amber-700',
                    \App\Models\AdmissionEnquiry::STATUS_APPROVED => 'bg-emerald-100 text-emerald-700',
                    \App\Models\AdmissionEnquiry::STATUS_REJECTED => 'bg-rose-100 text-rose-700',
                    \App\Models\AdmissionEnquiry::STATUS_CONTACTED => 'bg-violet-100 text-violet-700',
                ];
            @endphp
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Parent / Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Student</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Details</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Received</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($enquiries as $enquiry)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-semibold">{{ $enquiry->parent_name }}</p>
                            <p class="text-xs text-gray-500">{{ $enquiry->email ?: $enquiry->phone }}</p>
                            @if($enquiry->academic_year)
                                <p class="text-[11px] text-gray-400">{{ $enquiry->academic_year }}</p>
                            @endif
                            <span class="mt-2 inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-gray-700">
                                {{ $enquiry->inquiry_type }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-sm font-semibold">{{ $enquiry->student_name }}</p>
                            <p class="text-xs text-gray-500">{{ $enquiry->class_level }}</p>
                            @if($enquiry->current_school_name)
                                <p class="text-[11px] text-gray-400">{{ $enquiry->current_school_name }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            <div class="max-w-sm break-words space-y-1">
                                @if($enquiry->home_address)
                                    <p class="text-xs text-gray-500">{{ $enquiry->home_address }}</p>
                                @endif
                                <p>{{ $enquiry->message ?: 'No message provided.' }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $statusClasses[$enquiry->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst(str_replace('_', ' ', $enquiry->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ $enquiry->created_at->diffForHumans() }}
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.enquiries.show', $enquiry) }}"
                                class="text-blue-600 font-semibold hover:underline text-sm">Open record</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                            No admission records captured yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 px-6 py-3 bg-gray-50">
            {{ $enquiries->links() }}
        </div>
    </div>
</div>
@endsection
