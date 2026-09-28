<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_clearances', function (Blueprint $table) {
            $table->decimal('uniform_due', 12, 2)->default(0);
            $table->decimal('books_due', 12, 2)->default(0);
            $table->decimal('hostel_due', 12, 2)->default(0);
            $table->decimal('lunch_due', 12, 2)->default(0);
            $table->decimal('enrolment_due', 12, 2)->default(0);
        });

        Schema::create('fee_clearance_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_clearance_id')->constrained()->cascadeOnDelete();
            $table->string('category', 30);
            $table->decimal('amount', 12, 2);
            $table->date('paid_at');
            $table->string('payment_reference')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['fee_clearance_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_clearance_payments');
        Schema::table('fee_clearances', function (Blueprint $table) {
            $table->dropColumn(['uniform_due', 'books_due', 'hostel_due', 'lunch_due', 'enrolment_due']);
        });
    }
};
