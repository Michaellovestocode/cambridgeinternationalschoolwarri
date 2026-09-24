<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admission_enquiries', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->foreignId('enrolled_by')->nullable()->after('student_id')->constrained('users')->nullOnDelete();
            $table->timestamp('enrolled_at')->nullable()->after('enrolled_by');
        });
    }

    public function down(): void
    {
        Schema::table('admission_enquiries', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['enrolled_by']);
            $table->dropColumn(['student_id', 'enrolled_by', 'enrolled_at']);
        });
    }
};
