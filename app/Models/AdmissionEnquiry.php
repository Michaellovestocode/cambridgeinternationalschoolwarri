<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionEnquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'inquiry_type',
        'parent_name',
        'phone',
        'alternate_phone',
        'email',
        'student_name',
        'preferred_name',
        'student_gender',
        'student_date_of_birth',
        'academic_year',
        'nationality',
        'state_of_origin',
        'religious_affiliation',
        'native_language',
        'other_languages',
        'passport_country_number',
        'class_level',
        'previous_school',
        'parent_occupation',
        'home_address',
        'how_heard_about_us',
        'father_name',
        'father_home_address',
        'father_phone',
        'father_email',
        'father_company_name',
        'father_position_title',
        'father_office_phone',
        'father_office_email',
        'mother_name',
        'mother_home_address',
        'mother_phone',
        'mother_email',
        'mother_company_name',
        'mother_position_title',
        'mother_office_phone',
        'mother_office_email',
        'applicant_lives_with',
        'legal_guardian_name',
        'siblings_details',
        'has_siblings_applying',
        'siblings_applying_details',
        'transfer_state_town',
        'family_hospital_clinic',
        'current_school_name',
        'current_school_class',
        'current_school_address',
        'current_school_phone',
        'previous_schools',
        'extracurricular_activities',
        'learning_physical_limitation',
        'peculiar_illness',
        'diagnostic_information',
        'has_been_suspended_or_dismissed',
        'suspension_details',
        'previously_applied_to_cis',
        'previously_applied_year',
        'previously_attended_cis',
        'previously_attended_year',
        'heard_about_cis_through',
        'applying_to_other_schools',
        'other_school_name',
        'child_personality_notes',
        'undertaking_accepted',
        'message',
        'ip_address',
        'user_agent',
        'status',
        'admin_notes',
    ];

    public const STATUS_NEW = 'new';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CONTACTED = 'contacted';

    public const TYPE_ENQUIRY = 'enquiry';
    public const TYPE_APPLICATION = 'application';

    protected $casts = [
        'student_date_of_birth' => 'date',
        'has_siblings_applying' => 'boolean',
        'has_been_suspended_or_dismissed' => 'boolean',
        'previously_applied_to_cis' => 'boolean',
        'previously_attended_cis' => 'boolean',
        'applying_to_other_schools' => 'boolean',
        'undertaking_accepted' => 'boolean',
    ];

    public static function statuses(): array
    {
        return [
            self::STATUS_NEW,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_CONTACTED,
        ];
    }

    public static function inquiryTypes(): array
    {
        return [
            self::TYPE_ENQUIRY,
            self::TYPE_APPLICATION,
        ];
    }
}
