@extends('layouts.app')

@section('title', 'Edit Vital-Sign Record')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="rounded-2xl bg-gradient-to-r from-emerald-700 to-teal-700 p-6 text-white shadow-xl">
        <h1 class="text-2xl font-black">Edit Vital-Sign Record</h1>
        <p class="mt-1 text-emerald-100">Update the saved student check details.</p>
    </div>
    @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <form method="POST" action="{{ route('clinic.health-checks.update', $check) }}" class="space-y-5 rounded-2xl bg-white p-6 shadow">
        @csrf @method('PUT')
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2"><label class="mb-1 block text-sm font-bold text-gray-700">Student</label><select name="student_id" required class="w-full rounded-xl border border-gray-200 px-4 py-3"><option value="">Select student</option>@foreach($students as $student)<option value="{{ $student->id }}" @selected(old('student_id', $check->student_id) == $student->id)>{{ $student->name }}{{ $student->class ? ' — ' . $student->class->display_name : '' }}{{ $student->registration_number ? ' (' . $student->registration_number . ')' : '' }}</option>@endforeach</select></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Term / report title</label><input name="term_label" required maxlength="100" value="{{ old('term_label', $check->term_label) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3"></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Check type</label><input name="check_type" required maxlength="100" value="{{ old('check_type', $check->check_type) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3"></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Hostel</label><input name="hostel_name" maxlength="255" value="{{ old('hostel_name', $check->hostel_name) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3"></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Check date &amp; time</label><input type="datetime-local" name="checked_at" required value="{{ old('checked_at', $check->checked_at->format('Y-m-d\\TH:i')) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3"></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Temperature (°C)</label><input type="number" step="0.1" name="temperature" value="{{ old('temperature', $check->temperature) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3"></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Pulse (bpm)</label><input type="number" min="20" max="250" name="pulse" value="{{ old('pulse', $check->pulse) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3"></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Weight (kg)</label><input type="number" step="0.01" min="1" max="300" name="weight_kg" value="{{ old('weight_kg', $check->weight_kg) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3"></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Respiration (/min)</label><input type="number" min="1" max="100" name="respiration" value="{{ old('respiration', $check->respiration) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3"></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Blood pressure (mmHg)</label><input name="blood_pressure" maxlength="30" value="{{ old('blood_pressure', $check->blood_pressure) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3" placeholder="120/80"></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Status</label><select name="clearance_status" required class="w-full rounded-xl border border-gray-200 px-4 py-3"><option value="normal" @selected(old('clearance_status', $check->clearance_status) === 'normal')>Normal</option><option value="needs_observation" @selected(old('clearance_status', $check->clearance_status) === 'needs_observation')>Needs observation</option><option value="referred" @selected(old('clearance_status', $check->clearance_status) === 'referred')>Referred</option></select></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Remark</label><input name="remark" maxlength="255" value="{{ old('remark', $check->remark) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3"></div>
            <div class="sm:col-span-2"><label class="mb-1 block text-sm font-bold text-gray-700">Health notes</label><textarea name="health_notes" rows="3" maxlength="2000" class="w-full rounded-xl border border-gray-200 px-4 py-3">{{ old('health_notes', $check->health_notes) }}</textarea></div>
        </div>
        <div class="flex flex-wrap justify-end gap-3"><a href="{{ route('clinic.health-checks.recent') }}" class="rounded-xl bg-gray-100 px-5 py-3 text-sm font-bold text-gray-700">Cancel</a><button class="rounded-xl bg-emerald-700 px-5 py-3 text-sm font-black text-white">Save changes</button></div>
    </form>
</div>
@endsection
