@extends('layouts.app')

@section('title', 'Edit Teacher')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Teacher</h2>

        <form action="{{ route('admin.teacher.update', $teacher->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                <input type="text" name="name" value="{{ old('name', $teacher->name) }}" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                <input type="email" name="email" value="{{ old('email', $teacher->email) }}" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                @error('email')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Staff ID *</label>
                <input type="text" name="registration_number" value="{{ old('registration_number', $teacher->registration_number) }}" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                @error('registration_number')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Attendance Card ID</label>
                <input type="text" name="attendance_card_uid" value="{{ old('attendance_card_uid', $teacher->attendance_card_uid) }}"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500"
                       placeholder="Scan or type the card value">
                @error('attendance_card_uid')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Attendance Section</label>
                <select name="attendance_section" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="">-- Select Section --</option>
                    @foreach(['creche' => 'Creche / Early Years', 'primary' => 'Primary Section', 'junior_secondary' => 'Junior Secondary', 'senior_secondary' => 'Senior Secondary', 'admin_office' => 'Admin Office', 'other' => 'Other'] as $section => $label)
                        <option value="{{ $section }}" @selected(old('attendance_section', $teacher->attendance_section) === $section)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('attendance_section')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">WhatsApp Number</label>
                <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $teacher->whatsapp_number) }}"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500"
                       placeholder="+234...">
                @error('whatsapp_number')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Profile Picture</label>
                @if($teacher->photo)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $teacher->photo) }}" alt="Current Photo" class="w-20 h-20 rounded-full object-cover">
                    </div>
                @endif
                <input type="file" name="photo" accept="image/*"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                @error('photo')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Signature</label>
                @if($teacher->signature)
                    <div class="mb-2 rounded-lg border border-gray-200 bg-gray-50 p-3">
                        <img src="{{ asset('storage/' . $teacher->signature) }}" alt="Current Signature" class="h-16 w-48 object-contain">
                    </div>
                @endif
                <input type="file" name="signature" accept="image/*"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                <p class="text-xs text-gray-500 mt-1">Upload a new image only when you want to replace the current signature.</p>
                @error('signature')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Report Authority Role</label>
                <select name="report_authority_role" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="">No senior authority role</option>
                    <option value="head_teacher" @selected(old('report_authority_role', $teacher->report_authority_role) === 'head_teacher')>Head Teacher</option>
                    <option value="principal" @selected(old('report_authority_role', $teacher->report_authority_role) === 'principal')>Principal</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Used automatically on report cards. Secondary uses Principal; all other sections use Head Teacher.</p>
                @error('report_authority_role')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Password (leave blank to keep current)</label>
                <input type="password" name="password"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500"
                       placeholder="Enter new password or leave blank">
                @error('password')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <label class="flex items-start gap-3 rounded-xl border border-indigo-100 bg-indigo-50 p-4">
                <input type="checkbox" name="can_manage_blog" value="1" @checked(old('can_manage_blog', $teacher->can_manage_blog)) class="mt-1 rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500">
                <span>
                    <span class="block font-bold text-gray-900">Allow Blog Studio management</span>
                    <span class="block text-sm text-gray-600">This teacher keeps their teacher dashboard and also gets access to the Blog Studio moderation dashboard.</span>
                </span>
            </label>

            <label class="flex items-start gap-3 rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                <input type="checkbox" name="can_manage_attendance" value="1" @checked(old('can_manage_attendance', $teacher->can_manage_attendance)) class="mt-1 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500">
                <span>
                    <span class="block font-bold text-gray-900">Allow Attendance management</span>
                    <span class="block text-sm text-gray-600">This teacher can use the scanner and view attendance reports.</span>
                </span>
            </label>

            <label class="flex items-start gap-3 rounded-xl border border-amber-100 bg-amber-50 p-4">
                <input type="checkbox" name="can_review_report_cards" value="1" @checked(old('can_review_report_cards', $teacher->can_review_report_cards)) class="mt-1 rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                <span>
                    <span class="block font-bold text-gray-900">Allow Academic report review</span>
                    <span class="block text-sm text-gray-600">This teacher can review, edit scores, approve, or reject submitted report cards.</span>
                </span>
            </label>

            <div class="rounded-xl border border-amber-100 bg-white p-4">
                <p class="font-bold text-gray-900">Classes this reviewer can review</p>
                <p class="mt-1 text-sm text-gray-600">Tick only the classes this teacher is allowed to review.</p>
                @php
                    $selectedReviewClasses = collect(old('review_class_ids', $teacher->reportReviewClasses->pluck('id')->map(fn ($id) => (string) $id)->all()));
                @endphp
                <div class="mt-4 grid sm:grid-cols-2 gap-3">
                    @foreach($classes as $class)
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700">
                            <input type="checkbox" name="review_class_ids[]" value="{{ $class->id }}" @checked($selectedReviewClasses->contains((string) $class->id)) class="rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                            <span>{{ $class->display_name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('review_class_ids')<p class="text-red-500 text-sm mt-2">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold">
                    Update Teacher
                </button>
                <a href="{{ route('admin.teachers') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-8 py-3 rounded-lg font-semibold">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
