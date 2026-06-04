@extends('layouts.app')

@section('title', 'Admission Form Payments')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900">Admission Form Payments</h1>
            <p class="text-sm text-gray-500">Confirm manual transfers and issue application codes.</p>
        </div>
        <a href="{{ route('admin.enquiries.index') }}" class="text-sm text-blue-600 hover:underline">Admissions inbox</a>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        @php
            $summaryCards = [
                ['label' => 'Pending', 'value' => $summary[\App\Models\AdmissionFormPayment::STATUS_PENDING] ?? 0, 'class' => 'from-amber-400 to-orange-500'],
                ['label' => 'Approved', 'value' => $summary[\App\Models\AdmissionFormPayment::STATUS_APPROVED] ?? 0, 'class' => 'from-emerald-500 to-green-600'],
                ['label' => 'Rejected', 'value' => $summary[\App\Models\AdmissionFormPayment::STATUS_REJECTED] ?? 0, 'class' => 'from-rose-500 to-red-600'],
            ];
        @endphp

        @foreach($summaryCards as $card)
            <div class="rounded-2xl bg-gradient-to-br {{ $card['class'] }} p-5 text-white shadow-lg">
                <p class="text-xs font-semibold uppercase tracking-wide text-white/80">{{ $card['label'] }}</p>
                <p class="mt-3 text-3xl font-black">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="rounded-2xl bg-white p-6 shadow">
        <form method="GET" action="{{ route('admin.admission-form-payments.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label for="search" class="mb-1 block text-xs font-semibold text-gray-500">Search</label>
                <input id="search" name="search" type="text" value="{{ $filters['search'] }}"
                    placeholder="Parent, student, code, reference" class="w-full rounded-xl border border-gray-200 px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="status" class="mb-1 block text-xs font-semibold text-gray-500">Status</label>
                <select name="status" id="status" class="w-full rounded-xl border border-gray-200 px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">All statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ $filters['status'] === $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="w-full rounded-xl bg-blue-600 py-2 font-semibold text-white transition hover:bg-blue-700">Filter</button>
                <a href="{{ route('admin.admission-form-payments.index') }}" class="w-full rounded-xl border border-gray-200 py-2 text-center text-gray-700">Reset</a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl bg-white shadow">
        <div class="w-full overflow-x-auto">
            @php
                $statusClasses = [
                    \App\Models\AdmissionFormPayment::STATUS_PENDING => 'bg-amber-100 text-amber-700',
                    \App\Models\AdmissionFormPayment::STATUS_APPROVED => 'bg-emerald-100 text-emerald-700',
                    \App\Models\AdmissionFormPayment::STATUS_REJECTED => 'bg-rose-100 text-rose-700',
                ];
            @endphp
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Parent</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Student</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Payment</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Code</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($payments as $payment)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-semibold">{{ $payment->parent_name }}</p>
                                <p class="text-xs text-gray-500">{{ $payment->email ?: $payment->phone }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm font-semibold">{{ $payment->student_name }}</p>
                                <p class="text-xs text-gray-500">{{ $payment->class_level }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <p class="font-semibold">₦{{ number_format((float) $payment->amount_paid, 2) }}</p>
                                <p class="text-xs text-gray-500">{{ $payment->payment_reference ?: 'No reference' }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($payment->application_code)
                                    <span class="font-mono font-semibold text-gray-900">{{ $payment->application_code }}</span>
                                    @if($payment->application)
                                        <p class="text-xs text-emerald-600">Application submitted</p>
                                    @endif
                                @else
                                    <span class="text-gray-400">Not issued</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses[$payment->status] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.admission-form-payments.show', $payment) }}" class="text-sm font-semibold text-blue-600 hover:underline">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">No payment requests yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 bg-gray-50 px-6 py-3">
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection
