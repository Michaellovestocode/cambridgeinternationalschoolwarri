<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_attendance_machine_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('device_id', 100);
            $table->string('enroll_id', 100);
            $table->string('event_id', 64)->unique();
            $table->dateTime('punched_at');
            $table->string('direction', 10)->nullable();
            $table->text('raw_payload')->nullable();
            $table->foreignId('attendance_record_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index(['device_id', 'punched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_attendance_machine_events');
    }
};
