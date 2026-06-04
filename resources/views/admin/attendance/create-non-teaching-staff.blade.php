@extends('layouts.app')

@section('title', 'Add Non-teaching Staff')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="rounded-2xl bg-white p-5 shadow-xl sm:p-6">
        <h1 class="text-2xl font-black text-gray-900">Add Non-teaching Staff</h1>
        <p class="mt-1 text-sm text-gray-500">Create a profile that can be scanned for attendance.</p>

        <form method="POST" action="{{ route('admin.attendance.non-teaching-staff.store') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">Full Name *</label>
                <input name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-gray-200 px-4 py-3">
                @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">Staff ID *</label>
                <input name="registration_number" value="{{ old('registration_number') }}" required class="w-full rounded-xl border border-gray-200 px-4 py-3">
                @error('registration_number')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border border-gray-200 px-4 py-3">
                @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">Attendance Card ID</label>
                <input name="attendance_card_uid" value="{{ old('attendance_card_uid') }}" class="w-full rounded-xl border border-gray-200 px-4 py-3">
                @error('attendance_card_uid')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">Department</label>
                <select name="attendance_section" class="w-full rounded-xl border border-gray-200 px-4 py-3">
                    <option value="">-- Select Department --</option>
                    @foreach(['admin_office' => 'Admin Office', 'security' => 'Security', 'drivers' => 'Drivers', 'cleaners' => 'Cleaners', 'kitchen' => 'Kitchen / Catering', 'ict' => 'ICT', 'health' => 'Health / Nurse', 'maintenance' => 'Maintenance', 'other' => 'Other'] as $section => $label)
                        <option value="{{ $section }}" @selected(old('attendance_section') === $section)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('attendance_section')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">Password *</label>
                <input type="password" name="password" required class="w-full rounded-xl border border-gray-200 px-4 py-3">
                @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <button class="rounded-xl bg-emerald-600 px-5 py-3 font-bold text-white">Create Staff</button>
                <a href="{{ route('admin.attendance.people') }}" class="rounded-xl bg-gray-100 px-5 py-3 text-center font-bold text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
