<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('birthday_greetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('greeting_date');
            $table->text('message');
            $table->timestamp('expires_at');
            $table->string('guardian_phone', 30)->nullable();
            $table->boolean('whatsapp_requested')->default(false);
            $table->string('sms_status', 20)->default('not_requested');
            $table->timestamp('sms_sent_at')->nullable();
            $table->string('sms_error')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'greeting_date']);
            $table->index(['student_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('birthday_greetings');
    }
};
