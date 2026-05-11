<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            $table->string('class_teacher_name')->nullable()->after('class_teacher_comment');
            $table->string('class_teacher_signature')->nullable()->after('class_teacher_name');
            $table->date('class_teacher_signature_date')->nullable()->after('class_teacher_signature');
            $table->string('head_teacher_name')->nullable()->after('head_teacher_comment');
            $table->string('head_teacher_signature')->nullable()->after('head_teacher_name');
            $table->date('head_teacher_signature_date')->nullable()->after('head_teacher_signature');
            $table->date('next_term_begins')->nullable()->after('attendance_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            $table->dropColumn([
                'class_teacher_name',
                'class_teacher_signature',
                'class_teacher_signature_date',
                'head_teacher_name',
                'head_teacher_signature',
                'head_teacher_signature_date',
                'next_term_begins',
            ]);
        });
    }
};
