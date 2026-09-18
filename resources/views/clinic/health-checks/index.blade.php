@extends('layouts.app')

@section('title', 'Hostel Vital Signs')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="rounded-2xl bg-gradient-to-r from-emerald-700 to-teal-700 p-6 text-white shadow-xl sm:p-8">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-100">School Clinic</p>
        <h1 class="mt-2 text-3xl font-black">Hostel Vital Signs Check</h1>
        <p class="mt-2 text-emerald-100">Record the termly check-out vital signs for hostel students.</p>
    </div>

    @if(session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 font-semibold text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <form method="POST" action="{{ route('clinic.health-checks.store') }}" class="rounded-2xl bg-white p-5 shadow-xl sm:p-8">
        @csrf
        <div class="grid gap-4 md:grid-cols-3">
            <div class="md:col-span-2"><label class="mb-1 block text-sm font-bold text-gray-700">Student</label><select name="student_id" required class="w-full rounded-xl border border-gray-200 px-4 py-3"><option value="">Select student</option>@foreach($students as $student)<option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>{{ $student->name }}{{ $student->class ? ' — ' . $student->class->display_name : '' }}{{ $student->registration_number ? ' (' . $student->registration_number . ')' : '' }}</option>@endforeach</select></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Check date & time</label><input type="datetime-local" name="checked_at" value="{{ old('checked_at', now()->format('Y-m-d\\TH:i')) }}" required class="w-full rounded-xl border border-gray-200 px-4 py-3"></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Term / report title</label><input name="term_label" value="{{ old('term_label', 'Summer Term ' . now()->format('Y')) }}" required class="w-full rounded-xl border border-gray-200 px-4 py-3"></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Check type</label><input name="check_type" value="{{ old('check_type', 'Hostel check-out') }}" required class="w-full rounded-xl border border-gray-200 px-4 py-3"></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Hostel</label><input name="hostel_name" value="{{ old('hostel_name') }}" class="w-full rounded-xl border border-gray-200 px-4 py-3" placeholder="Optional"></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Temperature (°C)</label><input type="number" step="0.1" min="30" max="45" name="temperature" value="{{ old('temperature') }}" class="w-full rounded-xl border border-gray-200 px-4 py-3"></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Pulse (bpm)</label><input type="number" min="20" max="250" name="pulse" value="{{ old('pulse') }}" class="w-full rounded-xl border border-gray-200 px-4 py-3"></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Weight (kg)</label><input type="number" step="0.01" min="1" max="300" name="weight_kg" value="{{ old('weight_kg') }}" class="w-full rounded-xl border border-gray-200 px-4 py-3"></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Respiration (/min)</label><input type="number" min="1" max="100" name="respiration" value="{{ old('respiration') }}" class="w-full rounded-xl border border-gray-200 px-4 py-3"></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Blood pressure</label><input name="blood_pressure" value="{{ old('blood_pressure') }}" class="w-full rounded-xl border border-gray-200 px-4 py-3" placeholder="e.g. 120/80"></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Remark</label><input name="remark" value="{{ old('remark', 'Normal') }}" class="w-full rounded-xl border border-gray-200 px-4 py-3"></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Status</label><select name="clearance_status" required class="w-full rounded-xl border border-gray-200 px-4 py-3"><option value="normal" @selected(old('clearance_status', 'normal') === 'normal')>Normal</option><option value="needs_observation" @selected(old('clearance_status') === 'needs_observation')>Needs observation</option><option value="referred" @selected(old('clearance_status') === 'referred')>Referred</option></select></div>
        </div>
        <div class="mt-4"><label class="mb-1 block text-sm font-bold text-gray-700">Health notes</label><textarea name="health_notes" rows="3" class="w-full rounded-xl border border-gray-200 px-4 py-3" placeholder="Optional observations or follow-up notes">{{ old('health_notes') }}</textarea></div>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end"><a href="{{ route('clinic.dashboard') }}" class="rounded-xl bg-gray-100 px-5 py-3 text-center text-sm font-bold text-gray-700">Back to Dashboard</a><button class="rounded-xl bg-emerald-700 px-5 py-3 text-sm font-black text-white hover:bg-emerald-800">Save Vital Signs</button></div>
    </form>

    <section class="overflow-hidden rounded-2xl bg-white shadow-xl"><div class="border-b border-gray-100 p-5"><h2 class="text-xl font-black text-gray-900">Recent Health Checks</h2></div><div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr><th class="px-5 py-3">Student</th><th class="px-5 py-3">Vital signs</th><th class="px-5 py-3">Remark</th><th class="px-5 py-3">Checked</th></tr></thead><tbody class="divide-y divide-gray-100">@forelse($recentChecks as $check)<tr><td class="px-5 py-4 font-bold text-gray-900">{{ $check->student?->name }}<span class="block text-xs font-normal text-gray-500">{{ $check->student?->class?->display_name }}</span></td><td class="px-5 py-4 text-gray-700">{{ $check->temperature ? $check->temperature . ' °C' : '—' }} · {{ $check->pulse ? $check->pulse . ' bpm' : '—' }} · {{ $check->weight_kg ? $check->weight_kg . ' kg' : '—' }}</td><td class="px-5 py-4 text-gray-700">{{ $check->remark ?: '—' }}</td><td class="px-5 py-4 text-gray-700">{{ $check->checked_at->format('d M Y, g:i A') }}</td></tr>@empty<tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">No health checks have been recorded yet.</td></tr>@endforelse</tbody></table></div></section>
</div>
@endsection
