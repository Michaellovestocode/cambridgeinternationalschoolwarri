<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('developmental_skills', function (Blueprint $table) {
            $table->id();
            $table->string('section');
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('developmental_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('terms')->cascadeOnDelete();
            $table->foreignId('form_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('authority_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('authority_role')->nullable();
            $table->unsignedSmallInteger('days_school_opened')->nullable();
            $table->unsignedSmallInteger('days_present')->nullable();
            $table->unsignedSmallInteger('days_absent')->nullable();
            $table->text('class_teacher_remark')->nullable();
            $table->text('authority_remark')->nullable();
            $table->string('form_teacher_name')->nullable();
            $table->string('form_teacher_signature')->nullable();
            $table->date('form_teacher_signed_at')->nullable();
            $table->string('authority_name')->nullable();
            $table->string('authority_signature')->nullable();
            $table->date('authority_signed_at')->nullable();
            $table->string('status')->default('draft')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'session_id', 'term_id'], 'developmental_report_unique_term');
        });

        Schema::create('developmental_report_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('developmental_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('developmental_skill_id')->constrained()->cascadeOnDelete();
            $table->string('rating', 2)->nullable();
            $table->timestamps();

            $table->unique(['developmental_report_id', 'developmental_skill_id'], 'developmental_rating_unique_skill');
        });

        $now = now();
        $skills = [
            ['Communication Skills', 'Speaks clearly'],
            ['Communication Skills', 'Responds to direct questions'],
            ['Communication Skills', 'Follows routines and drills'],
            ['Communication Skills', 'Sings, incantates and poems'],
            ['Communication Skills', 'Responds to rhymes and drills'],
            ['Communication Skills', 'Understands opposites'],
            ['Social / Emotional Skills', 'Knows first and last name'],
            ['Social / Emotional Skills', 'Knows age'],
            ['Social / Emotional Skills', 'Follows direction'],
            ['Social / Emotional Skills', 'Shares well with others'],
            ['Social / Emotional Skills', 'Listens well'],
            ['Reading / Writing Skills', 'Knows how to say ABC sounds and names'],
            ['Reading / Writing Skills', 'Recognizes ABC sounds and names'],
            ['Reading / Writing Skills', 'Understands name with printed or written'],
            ['Numbers', 'Recognizes numbers one to ten'],
            ['Numbers', 'Understands empty and full'],
            ['Numbers', 'Understands more or less'],
            ['Numbers', 'Can identify numbers one to ten'],
            ['Numbers', 'Responds to counting of numbers'],
            ['Numbers', 'Can write numbers one to ten'],
            ['Color and Shape', 'Knows primary colors'],
            ['Color and Shape', 'Knows shapes and sizes'],
            ['Color and Shape', 'Knows size big/small'],
            ['Motor Skills', 'Can hold and use a pencil'],
            ['Motor Skills', 'Can hold and use a crayon'],
            ['Motor Skills', 'Can hold and use a spoon'],
            ['Motor Skills', 'Can walk forward and backward'],
            ['Motor Skills', 'Can clap hands'],
        ];

        foreach ($skills as $index => [$section, $name]) {
            DB::table('developmental_skills')->insert([
                'section' => $section,
                'name' => $name,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('developmental_report_ratings');
        Schema::dropIfExists('developmental_reports');
        Schema::dropIfExists('developmental_skills');
    }
};
