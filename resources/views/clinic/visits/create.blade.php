@extends('layouts.app')

@section('title', 'Record Clinic Visit')

@section('content')
<div class="mx-auto max-w-4xl space-y-5">
    <div class="rounded-2xl bg-gradient-to-r from-blue-700 to-cyan-700 p-6 text-white shadow-xl"><p class="text-xs font-bold uppercase tracking-wide text-blue-100">School Clinic</p><h1 class="mt-1 text-2xl font-black">Record New Visit</h1><p class="mt-2 text-sm text-blue-100">Record only the information needed for this care visit.</p></div>
    @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('clinic.visits.store') }}" class="space-y-6 rounded-2xl bg-white p-5 shadow-xl sm:p-8">
        @csrf
        <div class="grid gap-4 sm:grid-cols-3">
            <div><label class="mb-1 block text-sm font-bold text-gray-700">Person type</label><select name="patient_type" id="patient_type" required class="w-full rounded-xl border border-gray-200 px-4 py-3"><option value="student" {{ old('patient_type', 'student') === 'student' ? 'selected' : '' }}>Student</option><option value="staff" {{ old('patient_type') === 'staff' ? 'selected' : '' }}>Staff</option><option value="not_listed" {{ old('patient_type') === 'not_listed' ? 'selected' : '' }}>Not listed</option></select></div>
            <div id="class_picker"><label class="mb-1 block text-sm font-bold text-gray-700">Class</label><select name="class_id" id="class_id" class="w-full rounded-xl border border-gray-200 px-4 py-3"><option value="">Select class</option>@foreach($classes as $class)<option value="{{ $class->id }}" {{ old('class_id', $student?->class_id) == $class->id ? 'selected' : '' }}>{{ $class->display_name }}</option>@endforeach</select></div>
            <div id="person_picker"><label class="mb-1 block text-sm font-bold text-gray-700">Name</label><select name="person_id" id="person_id" class="w-full rounded-xl border border-gray-200 px-4 py-3"><option value="">Select class first</option></select></div>
        </div>
        <div id="not_listed_fields" class="hidden grid gap-4 rounded-xl bg-amber-50 p-4 sm:grid-cols-2"><div><label class="mb-1 block text-sm font-bold text-amber-900">Name as reported</label><input name="patient_name" value="{{ old('patient_name') }}" class="w-full rounded-xl border border-amber-200 px-4 py-3"></div><div><label class="mb-1 block text-sm font-bold text-amber-900">Student/staff ID, if known</label><input name="patient_identifier" value="{{ old('patient_identifier') }}" class="w-full rounded-xl border border-amber-200 px-4 py-3"></div><p class="text-xs text-amber-800 sm:col-span-2">Use this when the person is not in the list. Administration can identify and update the record later.</p></div>
        @if($student)<div class="rounded-xl bg-blue-50 p-4 text-sm font-bold text-blue-900">{{ $student->name }} · {{ $student->registration_number ?: 'No student ID' }} · {{ $student->class?->display_name }}</div>@endif
        <div class="grid gap-4 sm:grid-cols-2"><div><label class="mb-1 block text-sm font-bold text-gray-700">Date and time</label><input type="datetime-local" name="visited_at" value="{{ old('visited_at', now()->format('Y-m-d\TH:i')) }}" required class="w-full rounded-xl border border-gray-200 px-4 py-3"></div><div><label class="mb-1 block text-sm font-bold text-gray-700">Reason</label><input name="reason" value="{{ old('reason') }}" required class="w-full rounded-xl border border-gray-200 px-4 py-3" placeholder="Headache, injury, stomach pain..."></div></div>
        <div><label class="mb-1 block text-sm font-bold text-gray-700">Symptoms / complaint</label><textarea name="symptoms" rows="3" class="w-full rounded-xl border border-gray-200 px-4 py-3"></textarea></div>
        <div class="grid gap-4 sm:grid-cols-2"><div><label class="mb-1 block text-sm font-bold text-gray-700">Temperature</label><input type="number" step="0.1" min="30" max="45" name="temperature" class="w-full rounded-xl border border-gray-200 px-4 py-3" placeholder="°C"></div><div><label class="mb-1 block text-sm font-bold text-gray-700">Other vital notes</label><input name="vital_notes" class="w-full rounded-xl border border-gray-200 px-4 py-3" placeholder="Optional"></div></div>
        <div><label class="mb-1 block text-sm font-bold text-gray-700">Observation</label><textarea name="observation" rows="3" class="w-full rounded-xl border border-gray-200 px-4 py-3"></textarea></div>
        <div><label class="mb-1 block text-sm font-bold text-gray-700">First aid / action taken</label><textarea name="action_taken" rows="3" class="w-full rounded-xl border border-gray-200 px-4 py-3"></textarea></div>
        <div><label class="mb-1 block text-sm font-bold text-gray-700">Medication administered, if authorized</label><input name="medication_administered" class="w-full rounded-xl border border-gray-200 px-4 py-3"></div>
        <div class="grid gap-4 sm:grid-cols-2"><div><label class="mb-1 block text-sm font-bold text-gray-700">Outcome</label><select name="outcome" required class="w-full rounded-xl border border-gray-200 px-4 py-3"><option value="returned_to_class">Returned to class</option><option value="remained_in_clinic">Remained in clinic</option><option value="sent_home">Sent home</option><option value="referred_to_hospital">Referred to hospital / doctor</option><option value="other">Other</option></select></div><div class="flex items-center gap-3 rounded-xl bg-amber-50 p-4"><input type="checkbox" name="parent_contacted" value="1" class="rounded border-amber-300"><span class="text-sm font-bold text-amber-900">Parent / guardian contacted</span></div></div>
        <div><label class="mb-1 block text-sm font-bold text-gray-700">Parent contact time</label><input type="datetime-local" name="parent_contacted_at" class="w-full rounded-xl border border-gray-200 px-4 py-3 sm:max-w-sm"></div>
        <div><label class="mb-1 block text-sm font-bold text-gray-700">Additional notes</label><textarea name="notes" rows="3" class="w-full rounded-xl border border-gray-200 px-4 py-3"></textarea></div>
        <div class="flex flex-col gap-3 sm:flex-row sm:justify-end"><a href="{{ route('clinic.dashboard') }}" class="rounded-xl bg-gray-100 px-5 py-3 text-center text-sm font-bold text-gray-700">Cancel</a><button class="rounded-xl bg-blue-700 px-5 py-3 text-sm font-black text-white">Save Clinic Visit</button></div>
    </form>
</div>
<script>
    const classData = @json($classData);
    const staffData = @json($staffData);
    const type = document.getElementById('patient_type');
    const classPicker = document.getElementById('class_picker');
    const classSelect = document.getElementById('class_id');
    const personSelect = document.getElementById('person_id');
    const personPicker = document.getElementById('person_picker');
    const notListed = document.getElementById('not_listed_fields');
    const selectedPersonId = '{{ old('person_id', $student?->id) }}';
    function updatePeople() {
        const selectedType = type.value;
        classPicker.classList.toggle('hidden', selectedType !== 'student');
        personPicker.classList.toggle('hidden', selectedType === 'not_listed');
        notListed.classList.toggle('hidden', selectedType !== 'not_listed');
        personSelect.innerHTML = '<option value="">Select a person</option>';
        const people = selectedType === 'staff' ? staffData : (classData[classSelect.value] || []);
        people.forEach(person => {
            const option = new Option(person.name + (person.identifier ? ' · ' + person.identifier : '') + (person.role ? ' · ' + person.role : ''), person.id);
            personSelect.add(option);
        });
        if (selectedPersonId) personSelect.value = selectedPersonId;
    }
    type.addEventListener('change', updatePeople);
    classSelect.addEventListener('change', updatePeople);
    updatePeople();
</script>
@endsection
