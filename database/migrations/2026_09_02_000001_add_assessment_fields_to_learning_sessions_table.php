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
        Schema::table('learning_sessions', function (Blueprint $table) {
            $table->string('assessment_type', 20)->nullable()->default('quiz')->after('estimated_minutes');
            $table->string('assessment_format', 20)->nullable()->default('objective')->after('assessment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_sessions', function (Blueprint $table) {
            $table->dropColumn(['assessment_type', 'assessment_format']);
        });
    }
};
