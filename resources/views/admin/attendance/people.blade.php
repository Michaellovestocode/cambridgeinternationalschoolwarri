@extends('layouts.app')

@section('title', 'Attendance Cards')

@section('content')
<div class="mx-auto max-w-6xl space-y-5">
    <div class="rounded-2xl bg-white p-5 shadow-xl">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-black text-gray-900">Cards and Access</h1>
                <p class="text-sm text-gray-500">Assign scanner card IDs and allow selected staff to manage attendance.</p>
            </div>
            <a href="{{ route('admin.attendance.non-teaching-staff.create') }}" class="rounded-xl bg-slate-900 px-4 py-3 text-center text-sm font-bold text-white">Add Non-teaching Staff</a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif

    <form method="GET" class="grid gap-3 rounded-2xl bg-white p-4 shadow md:grid-cols-5">
        <input name="search" value="{{ $filters['search'] }}" placeholder="Name, ID, card" class="rounded-xl border border-gray-200 px-3 py-3 text-sm">
        <select name="role" class="rounded-xl border border-gray-200 px-3 py-3 text-sm">
            <option value="">All groups</option>
            @foreach(['student' => 'Students', 'teacher' => 'Teachers', 'non_teaching_staff' => 'Non-teaching staff', 'admin' => 'Admin'] as $role => $label)
                <option value="{{ $role }}" @selected($filters['role'] === $role)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="section" class="rounded-xl border border-gray-200 px-3 py-3 text-sm">
            <option value="">All sections</option>
            @foreach($sections as $section => $label)
                <option value="{{ $section }}" @selected($filters['section'] === $section)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="class_id" class="rounded-xl border border-gray-200 px-3 py-3 text-sm">
            <option value="">All classes</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected($filters['class_id'] == $class->id)>{{ $class->display_name }}</option>
            @endforeach
        </select>
        <button class="rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white">Filter</button>
    </form>

    <div class="space-y-3">
        @foreach($people as $person)
            <form method="POST" action="{{ route('admin.attendance.people.update', $person) }}" class="rounded-2xl bg-white p-4 shadow">
                @csrf
                @method('PUT')
                <div class="flex flex-col gap-3">
                    <div>
                        <p class="font-bold text-gray-900">{{ $person->name }}</p>
                        <p class="text-xs text-gray-500">{{ $person->registration_number }} • {{ ucfirst(str_replace('_', ' ', $person->role)) }}{{ $person->class ? ' • ' . $person->class->display_name : '' }}</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-[1fr,auto]">
                        <input name="attendance_card_uid" value="{{ old('attendance_card_uid', $person->attendance_card_uid) }}" placeholder="Card / barcode / QR value" class="rounded-xl border border-gray-200 px-3 py-3 text-sm">
                        <label class="flex items-center gap-2 rounded-xl bg-amber-50 px-3 py-3 text-sm font-semibold text-amber-800">
                            <input type="checkbox" name="can_manage_attendance" value="1" @checked(old('can_manage_attendance', $person->can_manage_attendance)) class="rounded border-amber-300 text-amber-600">
                            Can manage
                        </label>
                    </div>
                    @if(!$person->isStudent())
                        <select name="attendance_section" class="rounded-xl border border-gray-200 px-3 py-3 text-sm">
                            <option value="">Select section / department</option>
                            @foreach($sections as $section => $label)
                                <option value="{{ $section }}" @selected(old('attendance_section', $person->attendance_section) === $section)>{{ $label }}</option>
                            @endforeach
                        </select>
                    @endif
                    <button class="rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white sm:w-fit">Save</button>
                </div>
            </form>
        @endforeach
    </div>

    <div class="rounded-2xl bg-white px-4 py-3 shadow">{{ $people->links() }}</div>
</div>
@endsection
