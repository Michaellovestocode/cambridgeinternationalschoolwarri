<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Mathematics", "English Language"
            $table->string('code')->unique(); // e.g., "MTH", "ENG"
            $table->enum('category', ['core', 'elective', 'vocational'])->default('core');
            $table->string('class_level')->nullable(); // "Primary", "JSS", "SSS", or "All"
            $table->integer('order')->default(0); // For sorting on report card
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
