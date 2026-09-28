@extends('layouts.app')

@section('title', 'Add Offline Application')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900">Add Offline Application</h1>
            <p class="text-sm text-gray-500">Capture a walk-in or staff-assisted parent application.</p>
        </div>
        <a href="{{ route('admin.enquiries.index') }}" class="text-sm font-semibold text-blue-600 hover:underline">Back to admissions register</a>
    </div>

    <form action="{{ route('admin.enquiries.offline.store') }}" method="POST" class="space-y-6 rounded-2xl bg-white p-6 shadow">
        @csrf
        <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-900">This record will be labelled <strong>Offline / walk-in</strong> and will show the staff member who entered it.</div>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            @foreach ([['parent_name', 'Parent / guardian name', 'text', true], ['phone', 'Phone number', 'text', true], ['alternate_phone', 'Alternate phone', 'text', false], ['email', 'Email address', 'email', false], ['student_name', 'Student name', 'text', true], ['student_date_of_birth', 'Date of birth', 'date', false], ['academic_year', 'Academic year', 'text', false], ['current_school_name', 'Current school', 'text', false]] as [$name, $label, $type, $required])
                <div>
                    <label for="{{ $name }}" class="mb-1 block text-sm font-semibold text-gray-700">{{ $label }}{{ $required ? ' *' : '' }}</label>
                    <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name) }}" {{ $required ? 'required' : '' }} class="w-full rounded-xl border border-gray-200 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                    @error($name)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            @endforeach
            <div>
                <label for="student_gender" class="mb-1 block text-sm font-semibold text-gray-700">Gender</label>
                <select id="student_gender" name="student_gender" class="w-full rounded-xl border border-gray-200 px-3 py-2"><option value="">Not specified</option><option value="male" @selected(old('student_gender') === 'male')>Male</option><option value="female" @selected(old('student_gender') === 'female')>Female</option></select>
            </div>
        </div>
        <fieldset>
            <legend class="mb-2 block text-sm font-semibold text-gray-700">Class applying for <span class="text-red-600">*</span></legend>
            @error('class_level')<p class="mb-2 text-xs text-red-600">{{ $message }}</p>@enderror
            <div class="space-y-4">
                @foreach($sectionDefinitions as $sectionKey => $section)
                    @php($sectionClasses = $groupedClasses->get($sectionKey, collect()))
                    @if($sectionClasses->isNotEmpty())
                        <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                            <div class="bg-gradient-to-r {{ $section['color'] }} px-4 py-3 text-white">
                                <h2 class="font-bold">{{ $section['label'] }}</h2>
                                <p class="text-xs text-white/85">{{ $section['description'] }}</p>
                            </div>
                            <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach($sectionClasses as $class)
                                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 p-3 hover:border-indigo-400 hover:bg-indigo-50">
                                        <input type="radio" name="class_level" value="{{ $class->name }}" required @checked(old('class_level') === $class->name) class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm font-semibold text-gray-800">{{ $class->display_name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </section>
                    @endif
                @endforeach
            </div>
        </fieldset>
        @foreach($offlineSections as $sectionTitle => $fields)
            <section class="space-y-4 rounded-xl border border-gray-100 p-4">
                <h2 class="text-lg font-bold text-gray-900">{{ $sectionTitle }}</h2>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @foreach($fields as $field)
                        <div class="{{ $field[2] === 'textarea' ? 'md:col-span-2' : '' }}">
                            <label for="{{ $field[0] }}" class="mb-1 block text-sm font-semibold text-gray-700">{{ $field[1] }}</label>
                            @if($field[2] === 'textarea')
                                <textarea id="{{ $field[0] }}" name="{{ $field[0] }}" rows="3" class="w-full rounded-xl border border-gray-200 px-3 py-2">{{ old($field[0]) }}</textarea>
                            @else
                                <input id="{{ $field[0] }}" type="{{ $field[2] }}" name="{{ $field[0] }}" value="{{ old($field[0]) }}" class="w-full rounded-xl border border-gray-200 px-3 py-2">
                            @endif
                            @error($field[0])<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
                @if($sectionTitle === 'Student and family')
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach(['has_siblings_applying' => 'Has siblings applying?', 'applying_to_other_schools' => 'Applying to other schools?'] as $name => $label)
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="{{ $name }}" value="1" @checked(old($name))>{{ $label }}</label>
                        @endforeach
                    </div>
                @elseif($sectionTitle === 'Health, history, and additional information')
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach(['has_been_suspended_or_dismissed' => 'Previously suspended or dismissed?', 'previously_applied_to_cis' => 'Previously applied to CIS?', 'previously_attended_cis' => 'Previously attended CIS?'] as $name => $label)
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="{{ $name }}" value="1" @checked(old($name))>{{ $label }}</label>
                        @endforeach
                    </div>
                @endif
            </section>
        @endforeach
        <div>
            <label for="home_address" class="mb-1 block text-sm font-semibold text-gray-700">Home address</label>
            <textarea id="home_address" name="home_address" rows="3" required class="w-full rounded-xl border border-gray-200 px-3 py-2">{{ old('home_address') }}</textarea>
        </div>
        <div>
            <label for="message" class="mb-1 block text-sm font-semibold text-gray-700">Parent notes or enquiry</label>
            <textarea id="message" name="message" rows="3" class="w-full rounded-xl border border-gray-200 px-3 py-2">{{ old('message') }}</textarea>
        </div>
        <div>
            <label for="admin_notes" class="mb-1 block text-sm font-semibold text-gray-700">Internal notes</label>
            <textarea id="admin_notes" name="admin_notes" rows="3" class="w-full rounded-xl border border-gray-200 px-3 py-2">{{ old('admin_notes') }}</textarea>
        </div>
        <label class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950">
            <input type="checkbox" name="undertaking_accepted" value="1" @checked(old('undertaking_accepted')) class="mt-1">
            <span>Parent / guardian signed the undertaking on the paper application.</span>
        </label>
        <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700">Save offline application</button>
    </form>
</div>
@endsection
