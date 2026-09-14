<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('visited_at');
            $table->string('reason');
            $table->text('symptoms')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->string('vital_notes')->nullable();
            $table->text('observation')->nullable();
            $table->text('action_taken')->nullable();
            $table->string('medication_administered')->nullable();
            $table->boolean('parent_contacted')->default(false);
            $table->timestamp('parent_contacted_at')->nullable();
            $table->string('outcome');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['student_id', 'visited_at']);
            $table->index(['outcome', 'visited_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_visits');
    }
};
