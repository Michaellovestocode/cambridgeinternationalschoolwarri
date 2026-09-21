@extends('layouts.app')

@section('title', 'Add Student')

@section('content')
<div class="mx-auto max-w-6xl space-y-6 px-4 py-6">
    <div class="rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 p-6 text-white shadow-xl">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-100">Admissions</p>
                <h2 class="mt-2 text-3xl font-black">New Student Admission</h2>
            </div>
            <div class="flex gap-3">
                <button type="button" class="rounded-xl border border-white/30 bg-white/10 px-4 py-2 text-sm font-bold text-white hover:bg-white/20">Save Draft</button>
                <button type="submit" form="student-admission-form" class="rounded-xl bg-white px-4 py-2 text-sm font-black text-emerald-700 shadow hover:bg-emerald-50">Save & Enroll</button>
            </div>
        </div>
    </div>

    <form id="student-admission-form" action="{{ route('admin.student.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="rounded-2xl bg-white p-5 shadow-xl sm:p-8">
            <div class="mb-5 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 font-black text-emerald-700">1</div>
                <div>
                    <h3 class="text-xl font-black text-gray-900">Student Profile</h3>
                    <p class="text-sm text-gray-500">Basic identity and enrollment details.</p>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-bold text-gray-700">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-200" placeholder="Student full name">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold text-gray-700">Registration Number <span class="text-red-500">*</span></label>
                    <input type="text" name="registration_number" value="{{ old('registration_number') }}" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-200" placeholder="e.g., STD2024006">
                    @error('registration_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold text-gray-700">Attendance Card ID</label>
                    <input type="text" name="attendance_card_uid" value="{{ old('attendance_card_uid') }}"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-200" placeholder="Scan or type card value">
                    @error('attendance_card_uid')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold text-gray-700">Class <span class="text-red-500">*</span></label>
                    <select name="class_id" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        <option value="">-- Select Class --</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id', $preferredClassId ?? '') == $class->id ? 'selected' : '' }}>
                                {{ $class->display_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('class_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold text-gray-700">Date of Birth</label>
                    <input type="text" name="date_of_birth" value="{{ old('date_of_birth') }}"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-200" placeholder="DD/MM/YYYY" inputmode="numeric">
                    <p class="mt-1 text-xs text-gray-500">Use day/month/year, example 24/04/2012.</p>
                    @error('date_of_birth')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold text-gray-700">Sex</label>
                    <select name="sex" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        <option value="">-- Select Sex --</option>
                        <option value="male" {{ old('sex') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('sex') == 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                    @error('sex')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-bold text-gray-700">Profile Picture</label>
                    <div class="rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 p-3">
                        <input type="file" name="photo" accept="image/*" class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-xl file:border-0 file:bg-emerald-100 file:px-4 file:py-2 file:font-bold file:text-emerald-700 hover:file:bg-emerald-200">
                    </div>
                    @error('photo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-xl sm:p-8">
            <div class="mb-5 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 font-black text-blue-700">2</div>
                <div>
                    <h3 class="text-xl font-black text-gray-900">Parent / Guardian Information</h3>
                    <p class="text-sm text-gray-500">Contact details and home information.</p>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-bold text-gray-700">Parent Phone Number</label>
                    <input type="tel" name="parent_phone_number" value="{{ old('parent_phone_number') }}"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-200" placeholder="e.g., +2348012345678">
                    @error('parent_phone_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold text-gray-700">WhatsApp Number</label>
                    <input type="tel" value="{{ old('whatsapp_number') ?? old('parent_phone_number') }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-200" placeholder="Optional">
                    <p class="mt-1 text-xs text-gray-500">This field is informational only and not stored in the current student model.</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-xl sm:p-8">
            <div class="mb-5 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 font-black text-amber-700">3</div>
                <div>
                    <h3 class="text-xl font-black text-gray-900">School & Health Details</h3>
                    <p class="text-sm text-gray-500">Extra details useful for administration and health support.</p>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-bold text-gray-700">Club / Society</label>
                    <input type="text" name="club_society" value="{{ old('club_society') }}"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-200" placeholder="e.g., Science Club">
                    @error('club_society')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold text-gray-700">Favourite Colour</label>
                    <input type="text" name="favourite_colour" value="{{ old('favourite_colour') }}"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-200" placeholder="e.g., Blue">
                    @error('favourite_colour')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-xl sm:p-8">
            <div class="mb-5 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100 font-black text-violet-700">4</div>
                <div>
                    <h3 class="text-xl font-black text-gray-900">Account Setup</h3>
                    <p class="text-sm text-gray-500">Create the student account credentials.</p>
                </div>
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-gray-700">Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" required
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-200" placeholder="Enter secure password">
                @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.students') }}" class="rounded-xl bg-gray-200 px-5 py-3 text-center text-sm font-bold text-gray-700 transition hover:bg-gray-300">Cancel</a>
            <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-lg transition hover:bg-emerald-700">Save Student</button>
        </div>
    </form>
</div>
@endsection
