@extends('layouts.app')

@section('title', 'Fee Clearances')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">School Fee Clearances</h1>
                <p class="text-sm text-gray-500 mt-1">Track each fee item and its installments. Clearance approval still controls report-card access.</p>
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

    @if($selectedClassId)
        @php
            $scheduleClass = $classes->firstWhere('id', $selectedClassId);
        @endphp
        <div class="rounded-2xl bg-indigo-50 p-6 shadow">
            <h2 class="text-lg font-bold text-indigo-950">Class fee schedule: {{ $scheduleClass?->display_name }}</h2>
            <p class="mt-1 text-sm text-indigo-800">Set standard charges for the selected class, session, and term. Saving fills zero amounts and keeps existing non-zero student-specific amounts.</p>
            <form method="POST" action="{{ route('admin.fee-schedules.update') }}" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                <input type="hidden" name="session_id" value="{{ $selectedSessionId }}">
                <input type="hidden" name="term_id" value="{{ $selectedTermId }}">
                @foreach(['uniform' => 'Uniform', 'books' => 'Books', 'hostel' => 'Hostel', 'lunch' => 'Lunch', 'enrolment' => 'Enrolment'] as $key => $label)
                    @if($key !== 'enrolment' || in_array($scheduleClass?->level_number, [6, 9, 12], true))
                        <label class="text-sm font-semibold text-indigo-950">{{ $label }} amount
                            <input type="number" step="0.01" min="0" name="{{ $key }}_amount" value="{{ old($key . '_amount', $feeSchedule?->{$key . '_amount'}) }}" class="mt-1 w-full rounded-lg border border-indigo-200 bg-white px-3 py-2">
                        </label>
                    @endif
                @endforeach
                <button class="rounded-lg bg-indigo-700 px-4 py-2 font-bold text-white sm:col-span-2 lg:col-span-5">Save schedule and fill student fees</button>
            </form>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-600">
                        <th class="px-4 py-3 text-left">Student</th>
                        <th class="px-4 py-3 text-left">Uniform</th>
                        <th class="px-4 py-3 text-left">Books</th>
                        <th class="px-4 py-3 text-left">Hostel</th>
                        <th class="px-4 py-3 text-left">Lunch</th>
                        <th class="px-4 py-3 text-left">Enrolment</th>
                        <th class="px-4 py-3 text-left">Totals</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        @php
                            $clearance = $clearances[$student->id] ?? null;
                            $isApproved = (bool) ($clearance?->is_approved);
                            $hasEnrolmentFee = in_array($student->class?->level_number, [6, 9, 12], true);
                        @endphp
                        <tr class="border-t border-gray-100 align-top">
                            <td class="px-4 py-4">
                                <p class="font-semibold text-gray-900">{{ $student->name }}</p>
                                <p class="text-xs text-gray-500">{{ $student->registration_number }} &#183; {{ $student->class?->display_name ?? 'No class' }}</p>
                                <form id="fee-clearance-{{ $student->id }}" method="POST" action="{{ route('admin.fee-clearances.update', $student) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="session_id" value="{{ $selectedSessionId }}">
                                    <input type="hidden" name="term_id" value="{{ $selectedTermId }}">
                                </form>
                                <details class="mt-3 text-xs"><summary class="cursor-pointer font-semibold text-indigo-700">Installments and history</summary>
                                <form method="POST" action="{{ route('admin.fee-clearances.payments.store', $student) }}" class="mt-2 grid gap-2 rounded-lg bg-gray-50 p-3">
                                    @csrf
                                    <input type="hidden" name="session_id" value="{{ $selectedSessionId }}"><input type="hidden" name="term_id" value="{{ $selectedTermId }}">
                                    <select name="category" required class="rounded-lg border border-gray-300 px-3 py-2"><option value="">Payment category</option><option value="uniform">Uniform</option><option value="books">Books</option><option value="hostel">Hostel</option><option value="lunch">Lunch</option>@if($hasEnrolmentFee)<option value="enrolment">Enrolment</option>@endif</select>
                                    <input type="number" name="amount" step="0.01" min="0.01" required placeholder="Installment amount" class="rounded-lg border border-gray-300 px-3 py-2">
                                    <input type="date" name="paid_at" value="{{ now()->toDateString() }}" required class="rounded-lg border border-gray-300 px-3 py-2">
                                    <input type="text" name="payment_reference" placeholder="Receipt / reference" class="rounded-lg border border-gray-300 px-3 py-2">
                                    <button class="rounded-lg bg-indigo-600 px-3 py-2 font-semibold text-white">Record installment</button>
                                </form>
                                @if((float) ($clearance?->amount_paid ?? 0) > 0)<p class="mt-2 text-amber-800">Older unclassified payment: &#8358;{{ number_format($clearance->amount_paid, 2) }} {{ $clearance->payment_reference }}</p>@endif
                                @if($clearance?->payments?->isNotEmpty())
                                    <div class="mt-2 space-y-1">@foreach($clearance->payments as $payment)<p>{{ ucfirst($payment->category) }} &#183; &#8358;{{ number_format($payment->amount, 2) }} &#183; {{ $payment->paid_at->format('M j, Y') }}{{ $payment->payment_reference ? ' &#183; ' . $payment->payment_reference : '' }}</p>@endforeach</div>
                                @endif
                                </details>
                            </td>
                            @foreach(['uniform' => 'Uniform', 'books' => 'Books', 'hostel' => 'Hostel', 'lunch' => 'Lunch', 'enrolment' => 'Enrolment'] as $key => $label)
                                @php($due = (float) ($clearance?->{$key . '_due'} ?? 0))
                                @php($discount = min($due, (float) ($clearance?->{$key . '_discount'} ?? 0)))
                                @php($paid = (float) ($clearance?->payments->where('category', $key)->sum('amount') ?? 0))
                                <td class="px-3 py-4 align-top">
                                    @if($key !== 'enrolment' || $hasEnrolmentFee)
                                        <p class="whitespace-nowrap text-xs text-gray-600">Fee: &#8358;{{ number_format($due, 2) }}</p>
                                        <p class="whitespace-nowrap text-xs text-gray-600">Discount: &#8358;{{ number_format($discount, 2) }}</p>
                                        <p class="whitespace-nowrap text-xs text-gray-600">Paid: &#8358;<span data-category-paid="{{ $key }}">{{ number_format($paid, 2) }}</span></p>
                                        <p class="whitespace-nowrap text-xs font-semibold text-gray-900">Balance: &#8358;<span data-category-balance="{{ $key }}">{{ number_format(max(0, $due - $discount - $paid), 2) }}</span></p>
                                        <input form="fee-clearance-{{ $student->id }}" type="number" step="0.01" min="0" name="{{ $key }}_due" value="{{ old($key . '_due', $clearance?->{$key . '_due'}) }}" class="fee-due mt-2 w-28 rounded-lg border border-gray-300 px-2 py-2" data-category="{{ $key }}" placeholder="Amount due" aria-label="{{ $label }} amount due for {{ $student->name }}">
                                        <input form="fee-clearance-{{ $student->id }}" type="number" step="0.01" min="0" name="{{ $key }}_discount" value="{{ old($key . '_discount', $clearance?->{$key . '_discount'}) }}" class="fee-discount mt-1 w-28 rounded-lg border border-gray-300 px-2 py-2" data-category="{{ $key }}" placeholder="Discount" aria-label="{{ $label }} discount for {{ $student->name }}">
                                    @else
                                        <span class="text-gray-400">&mdash;</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="whitespace-nowrap px-3 py-4 text-xs" data-fee-totals data-fixed-paid="{{ $clearance ? max(0, $clearance->totalPaid() - $clearance->payments->sum('amount')) : 0 }}">
                                @php($paidTotal = $clearance?->totalPaid() ?? 0)
                                <p>Gross: &#8358;<span data-gross-total>{{ number_format($clearance?->grossTotal() ?? 0, 2) }}</span></p>
                                <p>Discount: &#8358;<span data-discount-total>{{ number_format($clearance?->totalDiscount() ?? 0, 2) }}</span></p>
                                <p class="font-bold">Payable: &#8358;<span data-payable-total>{{ number_format($clearance?->amountPayable() ?? 0, 2) }}</span></p>
                                <p>Paid: &#8358;<span data-paid-total>{{ number_format($paidTotal, 2) }}</span></p>
                                <p class="font-bold text-rose-700">Balance: &#8358;<span data-balance-total>{{ number_format($clearance?->balance() ?? 0, 2) }}</span></p>
                            </td>
                            <td class="px-4 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $isApproved ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $isApproved ? 'Approved' : 'Locked' }}</span>
                                @if($clearance?->approved_at)<p class="mt-2 text-xs text-gray-500">{{ $clearance->approved_at->format('M j, Y') }}</p>@endif
                            </td>
                            <td class="px-4 py-4 text-right">
                                <button type="submit" form="fee-clearance-{{ $student->id }}" name="is_approved" value="{{ $isApproved ? 1 : 0 }}" class="mb-2 whitespace-nowrap rounded-lg bg-slate-700 px-3 py-2 font-semibold text-white">Save amounts</button>
                                <button type="submit" form="fee-clearance-{{ $student->id }}"
                                        name="is_approved" value="{{ $isApproved ? 0 : 1 }}"
                                        class="{{ $isApproved ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white px-4 py-2 rounded-lg font-semibold">
                                    {{ $isApproved ? 'Revoke' : 'Approve' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-gray-500">No students found.</td>
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
<script>
document.querySelectorAll('tr').forEach((row) => {
    const totals = row.querySelector('[data-fee-totals]');
    if (!totals) return;
    const money = new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const recalculate = () => {
        let gross = 0;
        let discount = 0;
        let paid = Number(totals.dataset.fixedPaid || 0);
        row.querySelectorAll('.fee-due').forEach((input) => {
            const category = input.dataset.category;
            const amount = Number(input.value || 0);
            const discountInput = row.querySelector('.fee-discount[data-category="' + category + '"]');
            const categoryDiscount = Math.min(amount, Number(discountInput?.value || 0));
            const categoryPaid = Number(row.querySelector('[data-category-paid="' + category + '"]')?.textContent.replace(/,/g, '') || 0);
            gross += amount;
            discount += categoryDiscount;
            paid += categoryPaid;
            const balance = Math.max(0, amount - categoryDiscount - categoryPaid);
            const balanceElement = row.querySelector('[data-category-balance="' + category + '"]');
            if (balanceElement) balanceElement.textContent = money.format(balance);
        });
        const payable = Math.max(0, gross - discount);
        const values = { 'gross-total': gross, 'discount-total': discount, 'payable-total': payable, 'paid-total': paid, 'balance-total': Math.max(0, payable - paid) };
        Object.entries(values).forEach(([key, value]) => {
            const element = totals.querySelector('[data-' + key + ']');
            if (element) element.textContent = money.format(value);
        });
    };
    row.querySelectorAll('.fee-due, .fee-discount').forEach((input) => input.addEventListener('input', recalculate));
});
</script>
@endsection
