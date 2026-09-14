@extends('layouts.app')

@section('title', 'Student Clinic History')

@section('content')
<div class="mx-auto max-w-6xl space-y-5">
    @if(session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">{{ session('success') }}</div>@endif
    <div class="flex flex-col gap-4 rounded-2xl bg-white p-5 shadow sm:flex-row sm:items-center sm:justify-between">
        <div><p class="text-xs font-bold uppercase tracking-wide text-blue-700">Student Clinic Profile</p><h1 class="mt-1 text-2xl font-black text-gray-900">{{ $student->name }}</h1><p class="text-sm text-gray-500">{{ $student->registration_number ?: 'No student ID' }} · {{ $student->class?->display_name ?: 'No class' }} · {{ ucfirst($student->sex ?: 'Not recorded') }}</p></div>
        <div class="flex flex-col gap-2 sm:flex-row"><a href="{{ route('clinic.students.index') }}" class="rounded-xl bg-gray-100 px-4 py-3 text-center text-sm font-bold text-gray-700">Find Another</a><a href="{{ route('clinic.visits.create', ['student_id' => $student->id]) }}" class="rounded-xl bg-amber-400 px-4 py-3 text-center text-sm font-black text-slate-900">Record Visit</a></div>
    </div>
    <section class="rounded-2xl bg-white shadow-xl"><div class="border-b border-gray-100 p-5"><h2 class="text-xl font-black text-gray-900">Clinic History</h2><p class="text-sm text-gray-500">Previous visits are retained for authorized clinic users.</p></div><div class="divide-y divide-gray-100">
        @forelse($visits as $visit)
            <a href="{{ route('clinic.visits.show', $visit) }}" class="block p-5 hover:bg-blue-50"><div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between"><div><p class="font-bold text-gray-900">{{ $visit->reason }}</p><p class="mt-1 text-sm text-gray-600">{{ $visit->action_taken ?: 'No action recorded' }}</p></div><div class="text-left sm:text-right"><p class="text-sm font-bold text-gray-700">{{ $visit->visited_at->format('j M Y, g:i A') }}</p><p class="text-xs text-gray-500">{{ str_replace('_', ' ', ucfirst($visit->outcome)) }}</p></div></div></a>
        @empty
            <p class="p-8 text-center text-sm text-gray-500">No clinic history recorded.</p>
        @endforelse
    </div></section>
    {{ $visits->links() }}
</div>
@endsection
