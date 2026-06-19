<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scores', function (Blueprint $table) {
            $table->string('ca1_source')->nullable()->after('ca1');
            $table->decimal('ca1_original_cbt_score', 5, 2)->nullable()->after('ca1_source');
            $table->foreignId('ca1_overridden_by')->nullable()->after('ca1_original_cbt_score')->constrained('users')->nullOnDelete();
            $table->timestamp('ca1_overridden_at')->nullable()->after('ca1_overridden_by');
            $table->string('ca2_source')->nullable()->after('ca2');
            $table->decimal('ca2_original_cbt_score', 5, 2)->nullable()->after('ca2_source');
            $table->foreignId('ca2_overridden_by')->nullable()->after('ca2_original_cbt_score')->constrained('users')->nullOnDelete();
            $table->timestamp('ca2_overridden_at')->nullable()->after('ca2_overridden_by');
            $table->string('ca3_source')->nullable()->after('ca3');
            $table->decimal('ca3_original_cbt_score', 5, 2)->nullable()->after('ca3_source');
            $table->foreignId('ca3_overridden_by')->nullable()->after('ca3_original_cbt_score')->constrained('users')->nullOnDelete();
            $table->timestamp('ca3_overridden_at')->nullable()->after('ca3_overridden_by');
            $table->string('exam_source')->nullable()->after('exam');
            $table->decimal('exam_original_cbt_score', 5, 2)->nullable()->after('exam_source');
            $table->foreignId('exam_overridden_by')->nullable()->after('exam_original_cbt_score')->constrained('users')->nullOnDelete();
            $table->timestamp('exam_overridden_at')->nullable()->after('exam_overridden_by');
        });
    }

    public function down(): void
    {
        Schema::table('scores', function (Blueprint $table) {
            $table->dropColumn([
                'ca1_source',
                'ca1_original_cbt_score',
                'ca1_overridden_by',
                'ca1_overridden_at',
                'ca2_source',
                'ca2_original_cbt_score',
                'ca2_overridden_by',
                'ca2_overridden_at',
                'ca3_source',
                'ca3_original_cbt_score',
                'ca3_overridden_by',
                'ca3_overridden_at',
                'exam_source',
                'exam_original_cbt_score',
                'exam_overridden_by',
                'exam_overridden_at',
            ]);
        });
    }
};
