@extends('layouts.app')

@section('title', 'My Learners')

@section('content')
<div class="space-y-6">
    <div class="rounded-3xl bg-gradient-to-r from-emerald-600 to-indigo-600 p-6 text-white shadow-xl">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-white/70">Form Teacher</p>
                <h1 class="mt-2 text-3xl font-black">My Learners</h1>
                <p class="mt-2 text-white/85">{{ $formTeacher->schoolClass?->display_name ?? 'Assigned class' }}</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex rounded-2xl bg-white px-5 py-3 text-sm font-black text-indigo-700 shadow-lg hover:bg-indigo-50">Back to Dashboard</a>
        </div>
    </div>

    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm leading-6 text-amber-900">
        You can correct spelling mistakes in learner names here. Photo uploads, class changes, passwords, and registration details remain admin-only.
    </div>

    <div class="rounded-3xl bg-white p-5 shadow-lg">
        <form method="GET" action="{{ route('admin.form-teacher.learners') }}" class="flex flex-col gap-3 sm:flex-row">
            <input name="search" value="{{ request('search') }}" placeholder="Search by learner name or registration number" class="min-w-0 flex-1 rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
            <button class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-black text-white hover:bg-slate-800">Search</button>
            @if(request('search'))
                <a href="{{ route('admin.form-teacher.learners') }}" class="rounded-2xl border border-gray-200 px-5 py-3 text-center text-sm font-black text-gray-700 hover:bg-gray-50">Reset</a>
            @endif
        </form>
    </div>

    <div class="rounded-3xl bg-white shadow-lg">
        <div class="border-b border-gray-100 px-6 py-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-black text-gray-900">Class List</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ $students->count() }} learner{{ $students->count() === 1 ? '' : 's' }}</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            @if($students->isNotEmpty())
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-wider text-gray-500">Learner</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-wider text-gray-500">Registration</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-wider text-gray-500">Correct Name</th>
                            <th class="px-6 py-3 text-right text-xs font-black uppercase tracking-wider text-gray-500">Reports</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($students as $student)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($student->photo)
                                            <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->name }}" class="h-11 w-11 rounded-2xl object-cover">
                                        @else
                                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-sm font-black text-emerald-700">
                                                {{ strtoupper(substr($student->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-black text-gray-900">{{ $student->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $student->sex ? ucfirst($student->sex) : 'Sex not set' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-600">{{ $student->registration_number }}</td>
                                <td class="px-6 py-4">
                                    <form method="POST" action="{{ route('admin.form-teacher.learners.update-name', $student) }}" class="flex min-w-[18rem] gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input name="name" value="{{ old('name', $student->name) }}" required class="min-w-0 flex-1 rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                                        <button class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-black text-white hover:bg-emerald-700">Save</button>
                                    </form>
                                    @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.developmental-reports.edit', ['student' => $student]) }}" class="rounded-xl bg-teal-600 px-4 py-2 text-sm font-black text-white hover:bg-teal-700">Development</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="px-6 py-12 text-center text-gray-500">
                    No learners matched your search.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
