<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admission_enquiries', function (Blueprint $table) {
            $table->string('academic_year')->nullable()->after('inquiry_type');
            $table->string('preferred_name')->nullable()->after('student_name');
            $table->string('nationality')->nullable()->after('student_date_of_birth');
            $table->string('state_of_origin')->nullable()->after('nationality');
            $table->string('religious_affiliation')->nullable()->after('state_of_origin');
            $table->string('native_language')->nullable()->after('religious_affiliation');
            $table->string('other_languages')->nullable()->after('native_language');
            $table->string('passport_country_number')->nullable()->after('other_languages');

            $table->string('father_name')->nullable()->after('passport_country_number');
            $table->text('father_home_address')->nullable()->after('father_name');
            $table->string('father_phone')->nullable()->after('father_home_address');
            $table->string('father_email')->nullable()->after('father_phone');
            $table->string('father_company_name')->nullable()->after('father_email');
            $table->string('father_position_title')->nullable()->after('father_company_name');
            $table->string('father_office_phone')->nullable()->after('father_position_title');
            $table->string('father_office_email')->nullable()->after('father_office_phone');

            $table->string('mother_name')->nullable()->after('father_office_email');
            $table->text('mother_home_address')->nullable()->after('mother_name');
            $table->string('mother_phone')->nullable()->after('mother_home_address');
            $table->string('mother_email')->nullable()->after('mother_phone');
            $table->string('mother_company_name')->nullable()->after('mother_email');
            $table->string('mother_position_title')->nullable()->after('mother_company_name');
            $table->string('mother_office_phone')->nullable()->after('mother_position_title');
            $table->string('mother_office_email')->nullable()->after('mother_office_phone');

            $table->string('applicant_lives_with')->nullable()->after('mother_office_email');
            $table->string('legal_guardian_name')->nullable()->after('applicant_lives_with');
            $table->text('siblings_details')->nullable()->after('legal_guardian_name');
            $table->boolean('has_siblings_applying')->default(false)->after('siblings_details');
            $table->text('siblings_applying_details')->nullable()->after('has_siblings_applying');
            $table->string('transfer_state_town')->nullable()->after('siblings_applying_details');
            $table->string('family_hospital_clinic')->nullable()->after('transfer_state_town');

            $table->string('current_school_name')->nullable()->after('family_hospital_clinic');
            $table->string('current_school_class')->nullable()->after('current_school_name');
            $table->text('current_school_address')->nullable()->after('current_school_class');
            $table->string('current_school_phone')->nullable()->after('current_school_address');
            $table->text('previous_schools')->nullable()->after('current_school_phone');
            $table->text('extracurricular_activities')->nullable()->after('previous_schools');

            $table->text('learning_physical_limitation')->nullable()->after('extracurricular_activities');
            $table->text('peculiar_illness')->nullable()->after('learning_physical_limitation');
            $table->text('diagnostic_information')->nullable()->after('peculiar_illness');
            $table->boolean('has_been_suspended_or_dismissed')->default(false)->after('diagnostic_information');
            $table->text('suspension_details')->nullable()->after('has_been_suspended_or_dismissed');
            $table->boolean('previously_applied_to_cis')->default(false)->after('suspension_details');
            $table->string('previously_applied_year')->nullable()->after('previously_applied_to_cis');
            $table->boolean('previously_attended_cis')->default(false)->after('previously_applied_year');
            $table->string('previously_attended_year')->nullable()->after('previously_attended_cis');
            $table->string('heard_about_cis_through')->nullable()->after('previously_attended_year');
            $table->boolean('applying_to_other_schools')->default(false)->after('heard_about_cis_through');
            $table->string('other_school_name')->nullable()->after('applying_to_other_schools');

            $table->text('child_personality_notes')->nullable()->after('other_school_name');
            $table->boolean('undertaking_accepted')->default(false)->after('child_personality_notes');
        });
    }

    public function down(): void
    {
        Schema::table('admission_enquiries', function (Blueprint $table) {
            $table->dropColumn([
                'academic_year',
                'preferred_name',
                'nationality',
                'state_of_origin',
                'religious_affiliation',
                'native_language',
                'other_languages',
                'passport_country_number',
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
            ]);
        });
    }
};
