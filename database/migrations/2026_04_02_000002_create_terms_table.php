<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('academic_sessions')->onDelete('cascade');
            $table->string('name'); // "First Term", "Second Term", "Third Term"
            $table->integer('term_number'); // 1, 2, 3
            $table->date('start_date');
            $table->date('end_date');
            $table->date('next_term_begins')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terms');
    }
};
