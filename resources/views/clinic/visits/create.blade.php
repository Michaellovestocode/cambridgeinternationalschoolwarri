@extends('layouts.app')

@section('title', 'Record Clinic Visit')

@section('content')
<div class="mx-auto max-w-4xl space-y-5">
    <div class="rounded-2xl bg-gradient-to-r from-blue-700 to-cyan-700 p-6 text-white shadow-xl"><p class="text-xs font-bold uppercase tracking-wide text-blue-100">School Clinic</p><h1 class="mt-1 text-2xl font-black">Record New Visit</h1><p class="mt-2 text-sm text-blue-100">Record only the information needed for this care visit.</p></div>
    @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('clinic.visits.store') }}" class="space-y-6 rounded-2xl bg-white p-5 shadow-xl sm:p-8">
        @csrf
        <div><label class="mb-1 block text-sm font-bold text-gray-700">Student</label><input name="student_search" id="student_search" value="{{ $student?->name }}" placeholder="Search student by name or ID" class="w-full rounded-xl border border-gray-200 px-4 py-3" autocomplete="off"><input type="hidden" name="student_id" id="student_id" value="{{ $student?->id }}"><p class="mt-1 text-xs text-gray-500">Use Find Student first when possible, then open Record Visit.</p></div>
        @if($student)<div class="rounded-xl bg-blue-50 p-4 text-sm font-bold text-blue-900">{{ $student->name }} · {{ $student->registration_number }} · {{ $student->class?->display_name }}</div>@endif
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
@endsection
