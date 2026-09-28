<?php

namespace App\Http\Controllers;

use App\Mail\AdmissionEnquiryAcknowledgement;
use App\Mail\AdmissionEnquirySubmitted;
use App\Models\AdmissionEnquiry;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminAdmissionEnquiryController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index(Request $request)
    {
        $query = AdmissionEnquiry::query()->with('createdBy');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($source = $request->input('source')) {
            $query->where('entry_source', $source);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($sub) use ($search) {
                $sub->where('parent_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('student_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $enquiries = $query->latest()->paginate(15)->withQueryString();

        $summary = [
            AdmissionEnquiry::STATUS_NEW => AdmissionEnquiry::where('status', AdmissionEnquiry::STATUS_NEW)->count(),
            AdmissionEnquiry::STATUS_UNDER_REVIEW => AdmissionEnquiry::where('status', AdmissionEnquiry::STATUS_UNDER_REVIEW)->count(),
            AdmissionEnquiry::STATUS_APPROVED => AdmissionEnquiry::where('status', AdmissionEnquiry::STATUS_APPROVED)->count(),
            AdmissionEnquiry::STATUS_REJECTED => AdmissionEnquiry::where('status', AdmissionEnquiry::STATUS_REJECTED)->count(),
            AdmissionEnquiry::STATUS_CONTACTED => AdmissionEnquiry::where('status', AdmissionEnquiry::STATUS_CONTACTED)->count(),
        ];

        return view('admin.enquiries.index', [
            'enquiries' => $enquiries,
            'statuses' => AdmissionEnquiry::statuses(),
            'summary' => $summary,
            'filters' => [
                'status' => $request->string('status')->value(''),
                'source' => $request->string('source')->value(''),
                'search' => $request->string('search')->value(''),
            ],
        ]);
    }

    public function createOffline()
    {
        $classes = SchoolClass::query()->get()
            ->sort(fn (SchoolClass $first, SchoolClass $second) => $first->classSortKey() <=> $second->classSortKey())
            ->values();
        $groupedClasses = $classes->groupBy('section_key');

        return view('admin.enquiries.create-offline', [
            'groupedClasses' => $groupedClasses,
            'sectionDefinitions' => SchoolClass::sectionDefinitions(),
        ]);
    }

    public function storeOffline(Request $request)
    {
        $offlineFields = [
            'parent_name', 'phone', 'alternate_phone', 'email', 'student_name', 'preferred_name',
            'student_date_of_birth', 'student_gender', 'class_level', 'academic_year', 'home_address',
            'nationality', 'state_of_origin', 'religious_affiliation', 'native_language', 'other_languages',
            'passport_country_number', 'previous_school', 'parent_occupation', 'how_heard_about_us',
            'father_name', 'father_home_address', 'father_phone', 'father_email', 'father_company_name',
            'father_position_title', 'father_office_phone', 'father_office_email', 'mother_name',
            'mother_home_address', 'mother_phone', 'mother_email', 'mother_company_name',
            'mother_position_title', 'mother_office_phone', 'mother_office_email', 'applicant_lives_with',
            'legal_guardian_name', 'siblings_details', 'siblings_applying_details', 'transfer_state_town',
            'family_hospital_clinic', 'current_school_name', 'current_school_class', 'current_school_address',
            'current_school_phone', 'previous_schools', 'extracurricular_activities',
            'learning_physical_limitation', 'peculiar_illness', 'diagnostic_information', 'suspension_details',
            'previously_applied_year', 'previously_attended_year', 'heard_about_cis_through',
            'other_school_name', 'child_personality_notes', 'message', 'admin_notes',
        ];
        $rules = collect($offlineFields)->mapWithKeys(fn ($field) => [$field => ['nullable', 'string', 'max:255']])->all();
        foreach ([
            'home_address', 'father_home_address', 'mother_home_address', 'siblings_details', 'siblings_applying_details',
            'current_school_address', 'previous_schools', 'extracurricular_activities', 'learning_physical_limitation',
            'peculiar_illness', 'diagnostic_information', 'suspension_details', 'child_personality_notes', 'message', 'admin_notes',
        ] as $field) {
            $rules[$field] = ['nullable', 'string', 'max:4000'];
        }
        $rules['parent_name'] = ['required', 'string', 'max:255'];
        $rules['phone'] = ['required', 'string', 'max:50'];
        $rules['student_name'] = ['required', 'string', 'max:255'];
        $rules['home_address'] = ['required', 'string', 'max:2000'];
        $rules['email'] = ['nullable', 'email', 'max:255'];
        $rules['father_email'] = ['nullable', 'email', 'max:255'];
        $rules['father_office_email'] = ['nullable', 'email', 'max:255'];
        $rules['mother_email'] = ['nullable', 'email', 'max:255'];
        $rules['mother_office_email'] = ['nullable', 'email', 'max:255'];
        $rules['student_date_of_birth'] = ['nullable', 'date'];
        $rules['student_gender'] = ['nullable', 'in:male,female'];
        $rules['class_level'] = ['required', 'string', 'max:100', Rule::exists('school_classes', 'name')];
        foreach (['has_siblings_applying', 'has_been_suspended_or_dismissed', 'previously_applied_to_cis', 'previously_attended_cis', 'applying_to_other_schools', 'undertaking_accepted'] as $field) {
            $rules[$field] = ['nullable', 'boolean'];
        }
        $payload = $request->validate([
            ...$rules,
        ]);

        foreach (['has_siblings_applying', 'has_been_suspended_or_dismissed', 'previously_applied_to_cis', 'previously_attended_cis', 'applying_to_other_schools', 'undertaking_accepted'] as $field) {
            $payload[$field] = $request->boolean($field);
        }
        $payload['how_heard_about_us'] = $payload['heard_about_cis_through'] ?? null;

        $enquiry = AdmissionEnquiry::create([
            ...$payload,
            'inquiry_type' => AdmissionEnquiry::TYPE_APPLICATION,
            'entry_source' => AdmissionEnquiry::SOURCE_OFFLINE,
            'created_by' => $request->user()->id,
            'status' => AdmissionEnquiry::STATUS_NEW,
        ]);

        return redirect()
            ->route('admin.enquiries.show', $enquiry)
            ->with('success', 'Offline application saved in the admissions register.');
    }

    public function show(AdmissionEnquiry $enquiry)
    {
        return view('admin.enquiries.show', [
            'enquiry' => $enquiry,
            'statuses' => AdmissionEnquiry::statuses(),
            'classes' => SchoolClass::query()->get()
                ->sort(fn (SchoolClass $first, SchoolClass $second) => $first->classSortKey() <=> $second->classSortKey())
                ->values(),
        ]);
    }

    public function update(Request $request, AdmissionEnquiry $enquiry)
    {
        $payload = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', AdmissionEnquiry::statuses())],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $previousStatus = $enquiry->status;
        $enquiry->update($payload);

        // Forward the updated record to the configured admissions/admin email(s)
        try {
            Mail::to(config('admissions.email_to'))->send(new AdmissionEnquirySubmitted($enquiry));
        } catch (\Throwable $exception) {
            Log::warning('Admission enquiry admin forward email failed.', [
                'enquiry_id' => $enquiry->id,
                'target_email' => config('admissions.email_to'),
                'error' => $exception->getMessage(),
            ]);
        }
        if ($enquiry->status === AdmissionEnquiry::STATUS_CONTACTED && $previousStatus !== AdmissionEnquiry::STATUS_CONTACTED) {
            Log::info('Admission enquiry marked contacted', ['id' => $enquiry->id, 'parent' => $enquiry->parent_name]);
            if ($enquiry->email) {
                try {
                    Mail::to($enquiry->email)->send(new AdmissionEnquiryAcknowledgement($enquiry));
                } catch (\Throwable $exception) {
                    Log::warning('Admission acknowledgement email delivery failed.', [
                        'enquiry_id' => $enquiry->id,
                        'target_email' => $enquiry->email,
                        'error' => $exception->getMessage(),
                    ]);
                }
            }
        }

        return redirect()
            ->route('admin.enquiries.show', $enquiry)
            ->with('success', 'Admission record updated.');
    }

    public function enroll(Request $request, AdmissionEnquiry $enquiry)
    {
        abort_unless($enquiry->inquiry_type === AdmissionEnquiry::TYPE_APPLICATION, 404);

        if ($enquiry->status !== AdmissionEnquiry::STATUS_APPROVED) {
            return back()->with('error', 'Approve this application before enrolling the student.');
        }

        if ($enquiry->student_id) {
            return back()->with('error', 'This application is already linked to a student record.');
        }

        $validated = $request->validate([
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
        $class = SchoolClass::findOrFail($validated['class_id']);

        $duplicate = User::query()
            ->where('role', 'student')
            ->where('name', $enquiry->student_name)
            ->where('parent_phone_number', $enquiry->phone)
            ->when($enquiry->student_date_of_birth, fn ($query) => $query->whereDate('date_of_birth', $enquiry->student_date_of_birth))
            ->first();

        if ($duplicate) {
            return back()->with('error', 'A student with the same name and parent phone already exists. Review the possible match before enrolling.');
        }

        $student = DB::transaction(function () use ($request, $enquiry, $class, $validated) {
            $lockedEnquiry = AdmissionEnquiry::query()->lockForUpdate()->findOrFail($enquiry->id);
            if ($lockedEnquiry->student_id) {
                throw ValidationException::withMessages(['class_id' => 'This application has already been enrolled.']);
            }
            if ($lockedEnquiry->status !== AdmissionEnquiry::STATUS_APPROVED) {
                throw ValidationException::withMessages(['class_id' => 'Approve this application before enrolling the student.']);
            }

            do {
                $registrationNumber = 'CIS-' . now()->format('Y') . '-' . Str::upper(Str::random(6));
            } while (User::where('registration_number', $registrationNumber)->exists());

            $student = User::create([
                'name' => $enquiry->student_name,
                'registration_number' => $registrationNumber,
                'class_id' => $class->id,
                'password' => Hash::make($validated['password']),
                'role' => 'student',
                'date_of_birth' => $enquiry->student_date_of_birth,
                'parent_phone_number' => $enquiry->phone,
                'sex' => $enquiry->student_gender,
            ]);

            $lockedEnquiry->forceFill([
                'student_id' => $student->id,
                'enrolled_by' => $request->user()->id,
                'enrolled_at' => now(),
            ])->save();

            return $student;
        });

        return redirect()
            ->route('admin.student.edit', $student)
            ->with('success', 'Student enrolled and linked to this application. Registration number: ' . $student->registration_number);
    }
}
