<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_attendance_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('machine_id');
            $table->string('machine_user_id');
            $table->string('event_id')->unique();
            $table->timestamp('punched_at');
            $table->string('direction')->nullable();
            $table->json('payload')->nullable();
            $table->foreignId('attendance_record_id')->nullable()->constrained('attendance_records')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'punched_at']);
            $table->index(['machine_id', 'machine_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_attendance_events');
    }
};