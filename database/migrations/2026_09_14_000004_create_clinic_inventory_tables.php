<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('minimum_quantity', 10, 2)->default(0);
            $table->string('unit')->default('units');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('clinic_inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('clinic_inventory_items')->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->string('type');
            $table->decimal('quantity', 10, 2);
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_inventory_transactions');
        Schema::dropIfExists('clinic_inventory_items');
    }
};
