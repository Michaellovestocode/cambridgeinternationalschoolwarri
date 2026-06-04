<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_form_payments', function (Blueprint $table) {
            $table->id();
            $table->string('parent_name');
            $table->string('phone', 50);
            $table->string('email')->nullable();
            $table->string('student_name');
            $table->string('class_level', 100);
            $table->string('depositor_name');
            $table->date('payment_date')->nullable();
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->string('bank_name')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('payment_notes')->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('application_code')->nullable()->unique();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('application_code_used_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::table('admission_enquiries', function (Blueprint $table) {
            $table->foreignId('admission_form_payment_id')
                ->nullable()
                ->after('id')
                ->constrained('admission_form_payments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('admission_enquiries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admission_form_payment_id');
        });

        Schema::dropIfExists('admission_form_payments');
    }
};
