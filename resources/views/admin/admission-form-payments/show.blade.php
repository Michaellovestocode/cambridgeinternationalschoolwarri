@extends('layouts.app')

@section('title', 'Admission Form Payment')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900">Admission Form Payment</h1>
            <p class="text-sm text-gray-500">Confirm transfer details and issue the application code.</p>
        </div>
        <a href="{{ route('admin.admission-form-payments.index') }}" class="text-sm text-blue-600 hover:underline">Back to payment requests</a>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @php
        $statusClasses = [
            \App\Models\AdmissionFormPayment::STATUS_PENDING => 'bg-amber-100 text-amber-700',
            \App\Models\AdmissionFormPayment::STATUS_APPROVED => 'bg-emerald-100 text-emerald-700',
            \App\Models\AdmissionFormPayment::STATUS_REJECTED => 'bg-rose-100 text-rose-700',
        ];
    @endphp

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="space-y-4 rounded-2xl bg-white p-6 shadow">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Status</p>
                        <p class="text-lg font-bold text-gray-900">{{ ucfirst($payment->status) }}</p>
                    </div>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses[$payment->status] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Application Code</p>
                    @if($payment->application_code)
                        <p class="font-mono text-2xl font-black text-gray-900">{{ $payment->application_code }}</p>
                        <p class="mt-1 text-xs text-gray-500">Send this code to the parent after payment is confirmed.</p>
                    @else
                        <p class="text-sm text-gray-500">No code issued yet. Approving this request will generate one.</p>
                    @endif
                </div>

                @if($payment->application)
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                        <p class="text-sm font-semibold text-emerald-800">Application submitted</p>
                        <a href="{{ route('admin.enquiries.show', $payment->application) }}" class="mt-1 inline-block text-sm font-semibold text-emerald-700 hover:underline">Open admission record</a>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl bg-white p-6 shadow">
                <h3 class="mb-4 text-lg font-bold text-gray-900">Admin Action</h3>
                <form action="{{ route('admin.admission-form-payments.update', $payment) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="status" class="mb-1 block text-sm font-semibold text-gray-700">Status</label>
                        <select name="status" id="status" required class="w-full rounded-xl border border-gray-200 px-3 py-2">
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ $payment->status === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        @error('status')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="admin_notes" class="mb-1 block text-sm font-semibold text-gray-700">Admin Notes</label>
                        <textarea name="admin_notes" id="admin_notes" rows="5" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm">{{ old('admin_notes', $payment->admin_notes) }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Set status to approved after confirming the bank transfer. The system will generate the code.</p>
                        @error('admin_notes')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 font-semibold text-white">Save update</button>
                </form>
            </div>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-3 rounded-2xl bg-white p-6 shadow">
                    <h3 class="text-lg font-bold text-gray-900">Parent and Student</h3>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Parent / Guardian</p>
                        <p class="text-sm font-semibold text-gray-800">{{ $payment->parent_name }}</p>
                        <p class="text-sm text-gray-600">{{ $payment->phone }}</p>
                        <p class="text-sm text-gray-600">{{ $payment->email ?: 'No email provided' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Student</p>
                        <p class="text-sm font-semibold text-gray-800">{{ $payment->student_name }}</p>
                        <p class="text-sm text-gray-600">Applying for {{ $payment->class_level }}</p>
                    </div>
                </div>

                <div class="space-y-3 rounded-2xl bg-white p-6 shadow">
                    <h3 class="text-lg font-bold text-gray-900">Payment Details</h3>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Amount</p>
                        <p class="text-sm font-semibold text-gray-800">₦{{ number_format((float) $payment->amount_paid, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Depositor</p>
                        <p class="text-sm text-gray-700">{{ $payment->depositor_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Date / Bank / Reference</p>
                        <p class="text-sm text-gray-700">{{ $payment->payment_date?->format('F j, Y') ?: 'No date provided' }}</p>
                        <p class="text-sm text-gray-700">{{ $payment->bank_name ?: 'No bank provided' }}</p>
                        <p class="text-sm text-gray-700">{{ $payment->payment_reference ?: 'No reference provided' }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow">
                <h3 class="mb-3 text-lg font-bold text-gray-900">Payment Note</h3>
                <p class="text-sm leading-relaxed text-gray-700">{{ $payment->payment_notes ?: 'No payment note provided.' }}</p>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow">
                <h3 class="mb-3 text-lg font-bold text-gray-900">Request Metadata</h3>
                <p class="text-sm text-gray-600">Submitted {{ $payment->created_at->format('F j, Y g:i A') }}</p>
                <p class="text-sm text-gray-600">Approved {{ $payment->approved_at?->format('F j, Y g:i A') ?: 'Not approved yet' }}</p>
                <p class="text-sm text-gray-600">Code used {{ $payment->application_code_used_at?->format('F j, Y g:i A') ?: 'Not used yet' }}</p>
                <p class="text-xs text-gray-400">IP: {{ $payment->ip_address ?? 'Unknown' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
