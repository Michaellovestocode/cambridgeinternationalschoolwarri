@extends('layouts.app')

@section('title', 'Admission Record')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900">Admission Record</h1>
            <p class="text-sm text-gray-500">Review the application details and update the workflow status.</p>
        </div>
        <a href="{{ route('admin.enquiries.index') }}" class="text-sm text-blue-600 hover:underline">Back to admissions inbox</a>
    </div>

    @php
        $statusClasses = [
            \App\Models\AdmissionEnquiry::STATUS_NEW => 'bg-sky-100 text-sky-700',
            \App\Models\AdmissionEnquiry::STATUS_UNDER_REVIEW => 'bg-amber-100 text-amber-700',
            \App\Models\AdmissionEnquiry::STATUS_APPROVED => 'bg-emerald-100 text-emerald-700',
            \App\Models\AdmissionEnquiry::STATUS_REJECTED => 'bg-rose-100 text-rose-700',
            \App\Models\AdmissionEnquiry::STATUS_CONTACTED => 'bg-violet-100 text-violet-700',
        ];
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="space-y-6">
            <div class="bg-white shadow rounded-2xl p-6 space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Record Type</p>
                        <p class="text-lg font-bold text-gray-900">{{ ucfirst($enquiry->inquiry_type) }}</p>
                    </div>
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $statusClasses[$enquiry->status] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ ucfirst(str_replace('_', ' ', $enquiry->status)) }}
                    </span>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-500">Parent / Guardian</h3>
                    <p class="text-lg font-bold">{{ $enquiry->parent_name }}</p>
                    <p class="text-sm text-gray-500">{{ $enquiry->phone }}</p>
                    @if($enquiry->alternate_phone)
                        <p class="text-sm text-gray-500">Alt: {{ $enquiry->alternate_phone }}</p>
                    @endif
                    <p class="text-sm text-gray-500">{{ $enquiry->email ?? 'No email provided' }}</p>
                    @if($enquiry->parent_occupation)
                        <p class="text-sm text-gray-500">{{ $enquiry->parent_occupation }}</p>
                    @endif
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-500">Student</h3>
                    <p class="text-lg font-bold">{{ $enquiry->student_name }}</p>
                    <p class="text-sm text-gray-500">Applying for {{ $enquiry->class_level }}</p>
                    @if($enquiry->academic_year)
                        <p class="text-sm text-gray-500">Academic year: {{ $enquiry->academic_year }}</p>
                    @endif
                    @if($enquiry->preferred_name)
                        <p class="text-sm text-gray-500">Preferred name: {{ $enquiry->preferred_name }}</p>
                    @endif
                    @if($enquiry->student_gender)
                        <p class="text-sm text-gray-500">Gender: {{ ucfirst($enquiry->student_gender) }}</p>
                    @endif
                    @if($enquiry->student_date_of_birth)
                        <p class="text-sm text-gray-500">DOB: {{ $enquiry->student_date_of_birth->format('F j, Y') }}</p>
                    @endif
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-500">Submitted</h3>
                    <p class="text-sm text-gray-500">{{ $enquiry->created_at->format('F j, Y g:i A') }}</p>
                    <p class="text-xs text-gray-400">IP: {{ $enquiry->ip_address ?? 'Unknown' }}</p>
                </div>
            </div>

            <div class="bg-white shadow rounded-2xl p-6 space-y-3">
                <h3 class="text-lg font-bold text-gray-900">Background Details</h3>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Nationality / Origin</p>
                    <p class="text-sm text-gray-700">
                        {{ $enquiry->nationality ?: 'Not provided' }}
                        @if($enquiry->state_of_origin)
                            / {{ $enquiry->state_of_origin }}
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Religion / Languages</p>
                    <p class="text-sm text-gray-700">
                        {{ $enquiry->religious_affiliation ?: 'Not provided' }}
                        @if($enquiry->native_language)
                            | Native: {{ $enquiry->native_language }}
                        @endif
                    </p>
                    @if($enquiry->other_languages)
                        <p class="text-sm text-gray-700">Other: {{ $enquiry->other_languages }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Passport Country / Number</p>
                    <p class="text-sm text-gray-700">{{ $enquiry->passport_country_number ?: 'Not provided' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Previous School</p>
                    <p class="text-sm text-gray-700">{{ $enquiry->previous_school ?: 'Not provided' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Home Address</p>
                    <p class="text-sm text-gray-700">{{ $enquiry->home_address ?: 'Not provided' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">How They Heard About Us</p>
                    <p class="text-sm text-gray-700">{{ $enquiry->how_heard_about_us ?: 'Not provided' }}</p>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white shadow rounded-2xl p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-3">Parent Note</h3>
                <p class="text-sm text-gray-700 leading-relaxed">{{ $enquiry->message ?: 'No message provided.' }}</p>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <div class="bg-white shadow rounded-2xl p-6 space-y-3">
                    <h3 class="text-lg font-bold text-gray-900">Father Details</h3>
                    <p class="text-sm text-gray-700">{{ $enquiry->father_name ?: 'Not provided' }}</p>
                    <p class="text-sm text-gray-700">{{ $enquiry->father_phone ?: 'No phone provided' }}</p>
                    <p class="text-sm text-gray-700">{{ $enquiry->father_email ?: 'No email provided' }}</p>
                    <p class="text-sm text-gray-700">{{ $enquiry->father_company_name ?: 'No company provided' }}</p>
                    <p class="text-sm text-gray-700">{{ $enquiry->father_position_title ?: 'No position provided' }}</p>
                    @if($enquiry->father_office_phone || $enquiry->father_office_email)
                        <p class="text-sm text-gray-700">Office: {{ $enquiry->father_office_phone ?: 'No office phone' }} / {{ $enquiry->father_office_email ?: 'No office email' }}</p>
                    @endif
                    <p class="text-sm text-gray-700">{{ $enquiry->father_home_address ?: 'No home address provided' }}</p>
                </div>

                <div class="bg-white shadow rounded-2xl p-6 space-y-3">
                    <h3 class="text-lg font-bold text-gray-900">Mother Details</h3>
                    <p class="text-sm text-gray-700">{{ $enquiry->mother_name ?: 'Not provided' }}</p>
                    <p class="text-sm text-gray-700">{{ $enquiry->mother_phone ?: 'No phone provided' }}</p>
                    <p class="text-sm text-gray-700">{{ $enquiry->mother_email ?: 'No email provided' }}</p>
                    <p class="text-sm text-gray-700">{{ $enquiry->mother_company_name ?: 'No company provided' }}</p>
                    <p class="text-sm text-gray-700">{{ $enquiry->mother_position_title ?: 'No position provided' }}</p>
                    @if($enquiry->mother_office_phone || $enquiry->mother_office_email)
                        <p class="text-sm text-gray-700">Office: {{ $enquiry->mother_office_phone ?: 'No office phone' }} / {{ $enquiry->mother_office_email ?: 'No office email' }}</p>
                    @endif
                    <p class="text-sm text-gray-700">{{ $enquiry->mother_home_address ?: 'No home address provided' }}</p>
                </div>
            </div>

            <div class="bg-white shadow rounded-2xl p-6 space-y-4">
                <h3 class="text-lg font-bold text-gray-900">Family and School History</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Lives With / Guardian</p>
                        <p class="text-sm text-gray-700">{{ $enquiry->applicant_lives_with ?: 'Not provided' }}</p>
                        <p class="text-sm text-gray-700">{{ $enquiry->legal_guardian_name ?: 'No legal guardian listed' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Hospital / Clinic</p>
                        <p class="text-sm text-gray-700">{{ $enquiry->family_hospital_clinic ?: 'Not provided' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Siblings</p>
                        <p class="text-sm text-gray-700">{{ $enquiry->siblings_details ?: 'Not provided' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Siblings Applying</p>
                        <p class="text-sm text-gray-700">{{ $enquiry->has_siblings_applying ? 'Yes' : 'No' }}</p>
                        <p class="text-sm text-gray-700">{{ $enquiry->siblings_applying_details ?: 'No additional details' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Current School</p>
                        <p class="text-sm text-gray-700">{{ $enquiry->current_school_name ?: 'Not provided' }}</p>
                        <p class="text-sm text-gray-700">{{ $enquiry->current_school_class ?: '' }}</p>
                        <p class="text-sm text-gray-700">{{ $enquiry->current_school_phone ?: '' }}</p>
                        <p class="text-sm text-gray-700">{{ $enquiry->current_school_address ?: '' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Previous Schools / Activities</p>
                        <p class="text-sm text-gray-700">{{ $enquiry->previous_schools ?: 'Not provided' }}</p>
                        <p class="mt-2 text-sm text-gray-700">{{ $enquiry->extracurricular_activities ?: 'No extracurricular activities listed' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-2xl p-6 space-y-4">
                <h3 class="text-lg font-bold text-gray-900">Health, Conduct, and Admissions Notes</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Learning / Physical Limitation</p>
                        <p class="text-sm text-gray-700">{{ $enquiry->learning_physical_limitation ?: 'Not provided' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Peculiar Illness</p>
                        <p class="text-sm text-gray-700">{{ $enquiry->peculiar_illness ?: 'Not provided' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Diagnostic Information</p>
                        <p class="text-sm text-gray-700">{{ $enquiry->diagnostic_information ?: 'Not provided' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Suspension / Dismissal History</p>
                        <p class="text-sm text-gray-700">{{ $enquiry->has_been_suspended_or_dismissed ? 'Yes' : 'No' }}</p>
                        <p class="text-sm text-gray-700">{{ $enquiry->suspension_details ?: 'No details supplied' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Previous CIS Link</p>
                        <p class="text-sm text-gray-700">Applied before: {{ $enquiry->previously_applied_to_cis ? 'Yes' : 'No' }} {{ $enquiry->previously_applied_year ? '(' . $enquiry->previously_applied_year . ')' : '' }}</p>
                        <p class="text-sm text-gray-700">Attended before: {{ $enquiry->previously_attended_cis ? 'Yes' : 'No' }} {{ $enquiry->previously_attended_year ? '(' . $enquiry->previously_attended_year . ')' : '' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Other Admissions Info</p>
                        <p class="text-sm text-gray-700">Heard about CIS through: {{ $enquiry->heard_about_cis_through ?: ($enquiry->how_heard_about_us ?: 'Not provided') }}</p>
                        <p class="text-sm text-gray-700">Applying elsewhere: {{ $enquiry->applying_to_other_schools ? 'Yes' : 'No' }} {{ $enquiry->other_school_name ? '(' . $enquiry->other_school_name . ')' : '' }}</p>
                        <p class="text-sm text-gray-700">Transfer state / town: {{ $enquiry->transfer_state_town ?: 'Not provided' }}</p>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">About the Child</p>
                    <p class="text-sm text-gray-700">{{ $enquiry->child_personality_notes ?: 'Not provided' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Undertaking Accepted</p>
                    <p class="text-sm text-gray-700">{{ $enquiry->undertaking_accepted ? 'Yes' : 'No' }}</p>
                </div>
            </div>

            <div class="bg-white shadow rounded-2xl p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Admin Actions</h3>
                <form action="{{ route('admin.enquiries.update', $enquiry) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="status" class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                        <select name="status" id="status" required class="w-full border border-gray-200 rounded-xl px-3 py-2">
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ $enquiry->status === $status ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="admin_notes" class="block text-sm font-semibold text-gray-700 mb-1">Admin Notes</label>
                        <textarea name="admin_notes" id="admin_notes" rows="6"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">{{ old('admin_notes', $enquiry->admin_notes) }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">If you mark this record as contacted and an email address exists, the system will send an acknowledgement email.</p>
                        @error('admin_notes')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-xl font-semibold">
                        Save update
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
