<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('attendance_machine_user_id')->nullable()->unique()->after('attendance_card_uid');
        });

        Schema::table('attendance_records', function (Blueprint $table) {
            $table->string('source')->nullable()->after('checked_out_by');
            $table->string('machine_id')->nullable()->after('source');
            $table->string('machine_event_id')->nullable()->unique()->after('machine_id');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropUnique(['machine_event_id']);
            $table->dropColumn(['source', 'machine_id', 'machine_event_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['attendance_machine_user_id']);
            $table->dropColumn('attendance_machine_user_id');
        });
    }
};