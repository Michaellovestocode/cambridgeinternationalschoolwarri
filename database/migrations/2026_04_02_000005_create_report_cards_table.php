<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('session_id')->constrained('academic_sessions')->onDelete('cascade');
            $table->foreignId('term_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained('school_classes')->onDelete('cascade');
            
            // Summary data
            $table->decimal('total_score', 8, 2);
            $table->decimal('average_score', 5, 2);
            $table->integer('position');
            $table->integer('total_students');
            $table->string('overall_grade', 2)->nullable();
            
            // Grade distribution
            $table->json('grade_summary')->nullable(); // {"A": 5, "B": 3, "C": 2, etc.}
            
            // Attendance
            $table->integer('days_school_opened');
            $table->integer('days_present');
            $table->integer('days_absent');
            $table->decimal('attendance_percentage', 5, 2);
            
            // Comments
            $table->text('class_teacher_comment')->nullable();
            $table->text('head_teacher_comment')->nullable();
            
            // Customization
            $table->string('theme_color')->default('blue'); // blue, green, brown, pink
            
            // File paths
            $table->string('pdf_path')->nullable();
            $table->string('word_path')->nullable();
            
            // Status
            $table->enum('status', ['draft', 'generated', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['student_id', 'session_id', 'term_id'], 'unique_student_term_report');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_cards');
    }
};
