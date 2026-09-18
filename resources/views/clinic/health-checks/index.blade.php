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

    <form method="POST" action="{{ route('clinic.health-checks.store-batch') }}" class="rounded-2xl bg-white p-5 shadow-xl sm:p-8">
        @csrf
        <fieldset disabled class="hidden">
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
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end"><a href="{{ route('clinic.dashboard') }}" class="rounded-xl bg-gray-100 px-5 py-3 text-center text-sm font-bold text-gray-700">← Back to Dashboard</a><button class="rounded-xl bg-emerald-700 px-5 py-3 text-sm font-black text-white hover:bg-emerald-800">Save Vital Signs</button></div>
        </fieldset>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Term / report title</label><input name="term_label" value="{{ old('term_label', 'Summer Term ' . now()->format('Y')) }}" required class="w-full rounded-xl border border-gray-200 px-4 py-3"></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Check type</label><input name="check_type" value="{{ old('check_type', 'Hostel check-out') }}" required class="w-full rounded-xl border border-gray-200 px-4 py-3"></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Hostel</label><input name="hostel_name" value="{{ old('hostel_name') }}" class="w-full rounded-xl border border-gray-200 px-4 py-3" placeholder="Optional"></div>
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Check date & time</label><input type="datetime-local" name="checked_at" value="{{ old('checked_at', now()->format('Y-m-d\\TH:i')) }}" required class="w-full rounded-xl border border-gray-200 px-4 py-3"></div>
        </div>
        <div class="mt-5 flex flex-col gap-3 border-t border-gray-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
            <div><h2 class="text-xl font-black text-gray-900">Vital Signs Register</h2><p class="text-sm text-gray-500">Leave unused rows blank. Add rows when needed, then save the whole register once.</p></div>
            <button id="add-rows" type="button" class="rounded-xl bg-emerald-100 px-4 py-3 text-sm font-bold text-emerald-900 hover:bg-emerald-200">+ Add 5 rows</button>
        </div>
        <div class="mt-4 overflow-x-auto rounded-xl border border-gray-200">
            <table class="min-w-[1100px] w-full text-left text-sm">
                <thead class="bg-emerald-800 text-xs uppercase text-white"><tr><th class="w-12 px-3 py-3">S/N</th><th class="min-w-64 px-3 py-3">Student</th><th class="px-3 py-3">Temp °C</th><th class="px-3 py-3">Pulse</th><th class="px-3 py-3">Weight</th><th class="px-3 py-3">Respiration</th><th class="px-3 py-3">Blood pressure</th><th class="min-w-40 px-3 py-3">Remark</th><th class="min-w-40 px-3 py-3">Status</th><th class="w-14 px-3 py-3"></th></tr></thead>
                <tbody id="health-check-rows" class="divide-y divide-gray-100 bg-white"></tbody>
            </table>
        </div>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end"><a href="{{ route('clinic.dashboard') }}" class="rounded-xl bg-gray-100 px-5 py-3 text-center text-sm font-bold text-gray-700">Back to Dashboard</a><button class="rounded-xl bg-emerald-700 px-5 py-3 text-sm font-black text-white hover:bg-emerald-800">Save All Vital Signs</button></div>
    </form>

    <section class="overflow-hidden rounded-2xl bg-white shadow-xl"><div class="border-b border-gray-100 p-5"><h2 class="text-xl font-black text-gray-900">Recent Health Checks</h2></div><div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr><th class="px-5 py-3">Student</th><th class="px-5 py-3">Vital signs</th><th class="px-5 py-3">Remark</th><th class="px-5 py-3">Checked</th></tr></thead><tbody class="divide-y divide-gray-100">@forelse($recentChecks as $check)<tr><td class="px-5 py-4 font-bold text-gray-900">{{ $check->student?->name }}<span class="block text-xs font-normal text-gray-500">{{ $check->student?->class?->display_name }}</span></td><td class="px-5 py-4 text-gray-700">{{ $check->temperature ? $check->temperature . ' °C' : '—' }} · {{ $check->pulse ? $check->pulse . ' bpm' : '—' }} · {{ $check->weight_kg ? $check->weight_kg . ' kg' : '—' }}</td><td class="px-5 py-4 text-gray-700">{{ $check->remark ?: '—' }}</td><td class="px-5 py-4 text-gray-700">{{ $check->checked_at->format('d M Y, g:i A') }}</td></tr>@empty<tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">No health checks have been recorded yet.</td></tr>@endforelse</tbody></table></div></section>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const body = document.getElementById('health-check-rows');
    const students = @json($students->map(fn ($student) => ['id' => $student->id, 'label' => $student->name . ($student->class ? ' — ' . $student->class->display_name : '') . ($student->registration_number ? ' (' . $student->registration_number . ')' : '')])->values());
    const savedRows = @json(old('rows', array_fill(0, 10, [])));
    let rowIndex = 0;
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[character]));

    const addRow = (saved = {}) => {
        const index = rowIndex++;
        const row = document.createElement('tr');
        row.className = 'align-top';
        const number = (field, step, min, max) => `<input type="number" name="rows[${index}][${field}]" value="${escapeHtml(saved[field])}" step="${step}" min="${min}" max="${max}" class="w-full rounded-lg border border-gray-200 px-2 py-2 text-sm">`;
        row.innerHTML = `<td class="serial px-3 py-3 font-bold text-gray-500"></td>
            <td class="px-3 py-2"><select name="rows[${index}][student_id]" class="student w-full rounded-lg border border-gray-200 px-2 py-2 text-sm"><option value="">Select student</option></select></td>
            <td class="px-2 py-2">${number('temperature', '0.1', '30', '45')}</td>
            <td class="px-2 py-2">${number('pulse', '1', '20', '250')}</td>
            <td class="px-2 py-2">${number('weight_kg', '0.01', '1', '300')}</td>
            <td class="px-2 py-2">${number('respiration', '1', '1', '100')}</td>
            <td class="px-2 py-2"><input name="rows[${index}][blood_pressure]" value="${escapeHtml(saved.blood_pressure)}" maxlength="30" placeholder="120/80" class="w-full rounded-lg border border-gray-200 px-2 py-2 text-sm"></td>
            <td class="px-2 py-2"><input name="rows[${index}][remark]" value="${escapeHtml(saved.remark)}" maxlength="255" placeholder="Normal" class="w-full rounded-lg border border-gray-200 px-2 py-2 text-sm"></td>
            <td class="px-2 py-2"><select name="rows[${index}][clearance_status]" class="w-full rounded-lg border border-gray-200 px-2 py-2 text-sm"><option value="normal">Normal</option><option value="needs_observation">Observation</option><option value="referred">Referred</option></select></td>
            <td class="px-2 py-2 text-center"><button type="button" class="remove-row rounded-lg px-2 py-2 text-red-600 hover:bg-red-50" title="Remove row">×</button></td>`;
        const select = row.querySelector('.student');
        students.forEach((student) => {
            const option = new Option(student.label, student.id, false, String(saved.student_id || '') === String(student.id));
            select.add(option);
        });
        row.querySelector('[name$="[clearance_status]"]').value = saved.clearance_status || 'normal';
        row.querySelector('.remove-row').addEventListener('click', () => { row.remove(); renumber(); });
        body.appendChild(row);
        renumber();
    };

    const renumber = () => body.querySelectorAll('tr').forEach((row, index) => { row.querySelector('.serial').textContent = index + 1; });
    savedRows.forEach(addRow);
    document.getElementById('add-rows').addEventListener('click', () => { for (let index = 0; index < 5; index++) addRow(); });
});
</script>
@endsection
