<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic_visits', function (Blueprint $table) {
            $table->string('patient_sex', 20)->nullable()->after('patient_identifier');
            $table->date('patient_date_of_birth')->nullable()->after('patient_sex');
            $table->string('residence_type', 20)->nullable()->after('patient_date_of_birth');
        });
    }

    public function down(): void
    {
        Schema::table('clinic_visits', function (Blueprint $table) {
            $table->dropColumn(['patient_sex', 'patient_date_of_birth', 'residence_type']);
        });
    }
};
