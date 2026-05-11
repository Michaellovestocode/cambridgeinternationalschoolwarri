<?php

namespace App\Http\Controllers;

use App\Mail\AdmissionEnquirySubmitted;
use App\Models\AdmissionEnquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdmissionEnquiryController extends Controller
{
    public function create()
    {
        return view('apply');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->enquiryRules());

        $enquiry = $this->createAdmissionRecord($request, [
            ...$validated,
            'inquiry_type' => AdmissionEnquiry::TYPE_ENQUIRY,
        ]);

        $message = "Thank you! We've received your enquiry and will be in touch within 24 hours.";

        try {
            Mail::to(config('admissions.email_to'))->send(new AdmissionEnquirySubmitted($enquiry));
        } catch (\Throwable $exception) {
            Log::warning('Admission enquiry email delivery failed.', [
                'enquiry_id' => $enquiry->id,
                'target_email' => config('admissions.email_to'),
                'error' => $exception->getMessage(),
            ]);

            $message = "Thank you! We've received your enquiry. Our email notification is temporarily unavailable, but your enquiry was saved successfully.";
        }

        return response()->json([
            'message' => $message,
            'whatsapp_url' => $this->buildWhatsappUrl($enquiry),
        ]);
    }

    public function submitApplication(Request $request)
    {
        $validated = $request->validate($this->applicationRules());

        $application = $this->createAdmissionRecord($request, [
            ...$validated,
            'inquiry_type' => AdmissionEnquiry::TYPE_APPLICATION,
        ]);

        $flashMessage = 'Application submitted successfully. Our admissions team has been notified.';

        try {
            Mail::to(config('admissions.email_to'))->send(new AdmissionEnquirySubmitted($application));
        } catch (\Throwable $exception) {
            Log::warning('Admission application email delivery failed.', [
                'application_id' => $application->id,
                'target_email' => config('admissions.email_to'),
                'error' => $exception->getMessage(),
            ]);

            $flashMessage = 'Application submitted and saved successfully. Admin email notification is temporarily unavailable.';
        }

        return redirect()
            ->route('apply.create')
            ->with('success', $flashMessage);
    }

    private function enquiryRules(): array
    {
        return [
            'parent_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'student_name' => ['required', 'string', 'max:255'],
            'class_level' => ['required', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function applicationRules(): array
    {
        return [
            'parent_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'alternate_phone' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'academic_year' => ['nullable', 'string', 'max:100'],
            'student_name' => ['required', 'string', 'max:255'],
            'preferred_name' => ['nullable', 'string', 'max:255'],
            'student_gender' => ['nullable', 'in:male,female'],
            'student_date_of_birth' => ['nullable', 'date'],
            'class_level' => ['required', 'string', 'max:100'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'state_of_origin' => ['nullable', 'string', 'max:100'],
            'religious_affiliation' => ['nullable', 'string', 'max:100'],
            'native_language' => ['nullable', 'string', 'max:100'],
            'other_languages' => ['nullable', 'string', 'max:255'],
            'passport_country_number' => ['nullable', 'string', 'max:255'],
            'previous_school' => ['nullable', 'string', 'max:255'],
            'parent_occupation' => ['nullable', 'string', 'max:255'],
            'home_address' => ['required', 'string', 'max:2000'],
            'how_heard_about_us' => ['nullable', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'father_home_address' => ['nullable', 'string', 'max:2000'],
            'father_phone' => ['nullable', 'string', 'max:50'],
            'father_email' => ['nullable', 'email', 'max:255'],
            'father_company_name' => ['nullable', 'string', 'max:255'],
            'father_position_title' => ['nullable', 'string', 'max:255'],
            'father_office_phone' => ['nullable', 'string', 'max:50'],
            'father_office_email' => ['nullable', 'email', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'mother_home_address' => ['nullable', 'string', 'max:2000'],
            'mother_phone' => ['nullable', 'string', 'max:50'],
            'mother_email' => ['nullable', 'email', 'max:255'],
            'mother_company_name' => ['nullable', 'string', 'max:255'],
            'mother_position_title' => ['nullable', 'string', 'max:255'],
            'mother_office_phone' => ['nullable', 'string', 'max:50'],
            'mother_office_email' => ['nullable', 'email', 'max:255'],
            'applicant_lives_with' => ['nullable', 'string', 'max:100'],
            'legal_guardian_name' => ['nullable', 'string', 'max:255'],
            'siblings_details' => ['nullable', 'string', 'max:4000'],
            'has_siblings_applying' => ['nullable', 'boolean'],
            'siblings_applying_details' => ['nullable', 'string', 'max:2000'],
            'transfer_state_town' => ['nullable', 'string', 'max:255'],
            'family_hospital_clinic' => ['nullable', 'string', 'max:255'],
            'current_school_name' => ['nullable', 'string', 'max:255'],
            'current_school_class' => ['nullable', 'string', 'max:100'],
            'current_school_address' => ['nullable', 'string', 'max:2000'],
            'current_school_phone' => ['nullable', 'string', 'max:50'],
            'previous_schools' => ['nullable', 'string', 'max:4000'],
            'extracurricular_activities' => ['nullable', 'string', 'max:2000'],
            'learning_physical_limitation' => ['nullable', 'string', 'max:2000'],
            'peculiar_illness' => ['nullable', 'string', 'max:2000'],
            'diagnostic_information' => ['nullable', 'string', 'max:3000'],
            'has_been_suspended_or_dismissed' => ['nullable', 'boolean'],
            'suspension_details' => ['nullable', 'string', 'max:2000'],
            'previously_applied_to_cis' => ['nullable', 'boolean'],
            'previously_applied_year' => ['nullable', 'string', 'max:50'],
            'previously_attended_cis' => ['nullable', 'boolean'],
            'previously_attended_year' => ['nullable', 'string', 'max:50'],
            'heard_about_cis_through' => ['nullable', 'string', 'max:255'],
            'applying_to_other_schools' => ['nullable', 'boolean'],
            'other_school_name' => ['nullable', 'string', 'max:255'],
            'child_personality_notes' => ['nullable', 'string', 'max:4000'],
            'undertaking_accepted' => ['required', 'accepted'],
            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function createAdmissionRecord(Request $request, array $payload): AdmissionEnquiry
    {
        return AdmissionEnquiry::create([
            ...$payload,
            'how_heard_about_us' => $payload['how_heard_about_us'] ?? $payload['heard_about_cis_through'] ?? null,
            'has_siblings_applying' => $request->boolean('has_siblings_applying'),
            'has_been_suspended_or_dismissed' => $request->boolean('has_been_suspended_or_dismissed'),
            'previously_applied_to_cis' => $request->boolean('previously_applied_to_cis'),
            'previously_attended_cis' => $request->boolean('previously_attended_cis'),
            'applying_to_other_schools' => $request->boolean('applying_to_other_schools'),
            'undertaking_accepted' => $request->boolean('undertaking_accepted'),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);
    }

    private function buildWhatsappUrl(AdmissionEnquiry $enquiry): string
    {
        $schoolNumber = preg_replace('/\D+/', '', config('admissions.whatsapp_number'));
        $text = rawurlencode(
            "Hello, I just submitted an admission {$enquiry->inquiry_type}.\n".
            "Parent/Guardian: {$enquiry->parent_name}\n".
            "Phone: {$enquiry->phone}\n".
            'Email: '.($enquiry->email ?: 'Not provided')."\n".
            "Student: {$enquiry->student_name}\n".
            "Class Applying For: {$enquiry->class_level}\n".
            'Message: '.($enquiry->message ?: 'None')
        );

        return "https://wa.me/{$schoolNumber}?text={$text}";
    }
}
