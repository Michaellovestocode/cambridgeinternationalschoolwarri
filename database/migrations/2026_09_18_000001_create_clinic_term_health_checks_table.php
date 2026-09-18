<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_term_health_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->string('term_label');
            $table->string('check_type');
            $table->string('hostel_name')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->unsignedSmallInteger('pulse')->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->unsignedSmallInteger('respiration')->nullable();
            $table->string('blood_pressure', 30)->nullable();
            $table->text('health_notes')->nullable();
            $table->string('remark')->nullable();
            $table->string('clearance_status')->default('normal');
            $table->timestamp('checked_at');
            $table->timestamps();

            $table->index(['student_id', 'checked_at']);
            $table->index(['check_type', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_term_health_checks');
    }
};
