<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'teacher', 'student', 'parent', 'blog_manager', 'non_teaching_staff') DEFAULT 'student'");

        Schema::table('users', function (Blueprint $table) {
            $table->string('attendance_card_uid')->nullable()->unique()->after('registration_number');
            $table->boolean('can_manage_attendance')->default(false)->after('can_manage_blog');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['attendance_card_uid', 'can_manage_attendance']);
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'teacher', 'student', 'parent', 'blog_manager') DEFAULT 'student'");
    }
};
