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

    @php
        $schoolSections = \App\Models\SchoolClass::sectionDefinitions();
        $classesBySection = $classes->groupBy(fn ($class) => $class->section_key);
        $filterBase = array_filter([
            'search' => $filters['search'],
            'role' => $filters['role'],
        ]);
    @endphp
    <section class="rounded-2xl bg-white p-5 shadow">
        <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-lg font-black text-gray-900">Browse by school section</h2>
                <p class="text-sm text-gray-500">Choose a class card to show only its students—such as KG 1 IRIS.</p>
            </div>
            @if($filters['section'] || $filters['class_id'])
                <a href="{{ route('admin.attendance.people', $filterBase) }}" class="text-sm font-bold text-blue-600 hover:underline">Clear class selection</a>
            @endif
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($schoolSections as $sectionKey => $section)
                @php($sectionClasses = $classesBySection->get($sectionKey, collect()))
                @continue($sectionClasses->isEmpty())
                <div class="overflow-hidden rounded-2xl border {{ $section['soft'] }}">
                    <a href="{{ route('admin.attendance.people', array_merge($filterBase, ['section' => $sectionKey])) }}" class="block bg-gradient-to-r {{ $section['color'] }} px-4 py-3 text-white hover:brightness-105">
                        <p class="font-black">{{ $section['label'] }}</p>
                        <p class="text-xs text-white/85">{{ $sectionClasses->count() }} {{ Str::plural('class', $sectionClasses->count()) }} · {{ $section['description'] }}</p>
                    </a>
                    <div class="flex flex-wrap gap-2 p-3">
                        @foreach($sectionClasses as $class)
                            <a href="{{ route('admin.attendance.people', array_merge($filterBase, ['section' => $sectionKey, 'class_id' => $class->id])) }}"
                                class="rounded-xl border px-3 py-2 text-xs font-bold transition {{ (string) $filters['class_id'] === (string) $class->id ? 'border-slate-900 bg-slate-900 text-white' : 'border-white bg-white/80 text-gray-700 hover:border-gray-300 hover:bg-white' }}">
                                {{ $class->display_name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>

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
                        @if(in_array($person->role, ['non_teaching_staff', 'nurse'], true))
                            <select name="staff_role" class="rounded-xl border border-gray-200 px-3 py-3 text-sm">
                                <option value="non_teaching_staff" @selected($person->role === 'non_teaching_staff')>Non-teaching staff</option>
                                <option value="nurse" @selected($person->role === 'nurse')>School nurse</option>
                            </select>
                        @endif
                        <input name="attendance_card_uid" value="{{ old('attendance_card_uid', $person->attendance_card_uid) }}" placeholder="Card / barcode / QR value" class="rounded-xl border border-gray-200 px-3 py-3 text-sm">
                        @if(!$person->isStudent())
                            <input name="attendance_machine_user_id" value="{{ old('attendance_machine_user_id', $person->attendance_machine_user_id) }}" placeholder="F-G495 Enroll ID" class="rounded-xl border border-gray-200 px-3 py-3 text-sm">
                        @endif
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
