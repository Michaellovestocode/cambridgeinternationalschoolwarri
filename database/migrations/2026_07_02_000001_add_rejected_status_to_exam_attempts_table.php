<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE exam_attempts MODIFY status ENUM('in_progress', 'submitted', 'graded', 'rejected') DEFAULT 'in_progress'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('exam_attempts')
                ->where('status', 'rejected')
                ->update(['status' => 'in_progress']);

            DB::statement("ALTER TABLE exam_attempts MODIFY status ENUM('in_progress', 'submitted', 'graded') DEFAULT 'in_progress'");
        }
    }
};
