@extends('layouts.app')

@section('title', 'Clinic Students')

@section('content')
<div class="mx-auto max-w-6xl space-y-5">
    <div class="flex flex-col gap-4 rounded-2xl bg-white p-5 shadow sm:flex-row sm:items-center sm:justify-between">
        <div><p class="text-xs font-bold uppercase tracking-wide text-blue-700">School Clinic</p><h1 class="mt-1 text-2xl font-black text-gray-900">Find a Student</h1><p class="text-sm text-gray-500">Search by name, student ID, or class.</p></div>
        <a href="{{ route('clinic.dashboard') }}" class="rounded-xl bg-gray-100 px-4 py-3 text-center text-sm font-bold text-gray-700">Dashboard</a>
    </div>
    <form method="GET" class="flex flex-col gap-3 rounded-2xl bg-white p-4 shadow sm:flex-row">
        <input name="search" value="{{ $search }}" class="min-w-0 flex-1 rounded-xl border border-gray-200 px-4 py-3" placeholder="Student name, admission number, or class">
        <button class="rounded-xl bg-blue-700 px-5 py-3 font-bold text-white">Search</button>
    </form>
    <div class="grid gap-3">
        @forelse($students as $student)
            <a href="{{ route('clinic.students.show', $student) }}" class="rounded-2xl bg-white p-5 shadow hover:bg-blue-50">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"><div><p class="font-bold text-gray-900">{{ $student->name }}</p><p class="text-sm text-gray-500">{{ $student->registration_number ?: 'No student ID' }} · {{ $student->class?->display_name ?: 'No class' }}</p></div><span class="text-sm font-bold text-blue-700">Open clinic history</span></div>
            </a>
        @empty
            <p class="rounded-2xl bg-white p-8 text-center text-sm text-gray-500">No students found.</p>
        @endforelse
    </div>
    {{ $students->links() }}
</div>
@endsection
