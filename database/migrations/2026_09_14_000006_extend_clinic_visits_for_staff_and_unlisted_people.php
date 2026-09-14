<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic_visits', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreignId('student_id')->nullable()->change();
            $table->foreignId('person_id')->nullable()->after('student_id')->constrained('users')->nullOnDelete();
            $table->string('patient_type')->default('student')->after('person_id');
            $table->string('patient_name')->nullable()->after('patient_type');
            $table->string('patient_identifier')->nullable()->after('patient_name');
            $table->index(['patient_type', 'visited_at']);
            $table->dropIndex(['student_id', 'visited_at']);
            $table->index(['student_id', 'visited_at']);
            $table->foreign('student_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clinic_visits', function (Blueprint $table) {
            $table->dropForeign(['person_id']);
            $table->dropForeign(['student_id']);
            $table->dropIndex(['patient_type', 'visited_at']);
            $table->dropColumn(['person_id', 'patient_type', 'patient_name', 'patient_identifier']);
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
