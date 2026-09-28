<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_fee_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('terms')->cascadeOnDelete();
            $table->decimal('uniform_amount', 12, 2)->default(0);
            $table->decimal('books_amount', 12, 2)->default(0);
            $table->decimal('hostel_amount', 12, 2)->default(0);
            $table->decimal('lunch_amount', 12, 2)->default(0);
            $table->decimal('enrolment_amount', 12, 2)->default(0);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['class_id', 'session_id', 'term_id']);
        });

        Schema::table('fee_clearances', function (Blueprint $table) {
            $table->decimal('uniform_discount', 12, 2)->default(0);
            $table->decimal('books_discount', 12, 2)->default(0);
            $table->decimal('hostel_discount', 12, 2)->default(0);
            $table->decimal('lunch_discount', 12, 2)->default(0);
            $table->decimal('enrolment_discount', 12, 2)->default(0);
            $table->foreignId('fee_schedule_id')->nullable()->constrained('class_fee_schedules')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fee_clearances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fee_schedule_id');
            $table->dropColumn([
                'uniform_discount', 'books_discount', 'hostel_discount', 'lunch_discount', 'enrolment_discount',
            ]);
        });
        Schema::dropIfExists('class_fee_schedules');
    }
};
