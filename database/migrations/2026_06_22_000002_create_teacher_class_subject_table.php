<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_class_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['teacher_id', 'school_class_id', 'subject_id'], 'teacher_class_subject_unique');
            $table->index(['teacher_id', 'subject_id']);
        });

        $now = now();
        $teacherClasses = DB::table('teacher_class')->get();

        foreach ($teacherClasses as $teacherClass) {
            $subjectIds = DB::table('teacher_subject')
                ->where('teacher_id', $teacherClass->teacher_id)
                ->pluck('subject_id');

            foreach ($subjectIds as $subjectId) {
                DB::table('teacher_class_subject')->updateOrInsert(
                    [
                        'teacher_id' => $teacherClass->teacher_id,
                        'school_class_id' => $teacherClass->school_class_id,
                        'subject_id' => $subjectId,
                    ],
                    [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_class_subject');
    }
};
