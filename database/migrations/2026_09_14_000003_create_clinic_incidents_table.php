<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('incident_at');
            $table->string('incident_type');
            $table->string('location')->nullable();
            $table->text('description');
            $table->text('injury_details')->nullable();
            $table->text('action_taken')->nullable();
            $table->string('referred_to')->nullable();
            $table->boolean('parent_contacted')->default(false);
            $table->timestamp('parent_contacted_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_incidents');
    }
};
