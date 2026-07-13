<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('developmental_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('developmental_reports', 'attendance_percentage')) {
                $table->decimal('attendance_percentage', 5, 2)->nullable()->after('days_absent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('developmental_reports', function (Blueprint $table) {
            if (Schema::hasColumn('developmental_reports', 'attendance_percentage')) {
                $table->dropColumn('attendance_percentage');
            }
        });
    }
};
