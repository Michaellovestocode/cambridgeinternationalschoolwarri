<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('session_id')->constrained('academic_sessions')->onDelete('cascade');
            $table->foreignId('term_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained('school_classes')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            
            // Nigerian CA structure: CA1 + CA2 + CA3 + Exam
            $table->decimal('ca1', 5, 2)->default(0)->nullable(); // Max 10
            $table->decimal('ca2', 5, 2)->default(0)->nullable(); // Max 10
            $table->decimal('ca3', 5, 2)->default(0)->nullable(); // Max 10
            $table->decimal('exam', 5, 2)->default(0)->nullable(); // Max 70
            $table->decimal('total', 5, 2)->default(0); // Sum of all (Max 100)
            
            // Grading
            $table->string('grade', 2)->nullable(); // A1, B2, C4, etc.
            $table->string('remark')->nullable(); // Excellent, Very Good, etc.
            
            // Position in class
            $table->integer('position')->nullable();
            $table->integer('total_students')->nullable();
            
            // Class average for this subject
            $table->decimal('class_average', 5, 2)->nullable();
            
            // Workflow status
            $table->enum('status', ['draft', 'submitted', 'approved', 'published'])->default('draft');
            
            $table->text('teacher_comment')->nullable();
            $table->timestamps();
            
            // Unique constraint: one score record per student per subject per term
            $table->unique(['student_id', 'subject_id', 'session_id', 'term_id'], 'unique_student_subject_term');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
