<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_name')->default('Cambridge International School');
            $table->text('school_address')->nullable();
            $table->string('school_phone')->nullable();
            $table->string('school_email')->nullable();
            $table->string('school_website')->nullable();
            $table->string('school_logo')->nullable(); // Logo file path
            $table->string('school_motto')->nullable();
            $table->string('principal_name')->nullable();
            $table->string('principal_signature')->nullable(); // Signature image path
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
