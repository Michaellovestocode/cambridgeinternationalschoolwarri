<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            $table->json('subjects')->nullable()->after('overall_grade');
            $table->text('overall_remark')->nullable()->after('head_teacher_comment');
            $table->text('teacher_comment')->nullable()->after('overall_remark');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['subjects', 'overall_remark', 'teacher_comment', 'created_by']);
        });
    }
};
