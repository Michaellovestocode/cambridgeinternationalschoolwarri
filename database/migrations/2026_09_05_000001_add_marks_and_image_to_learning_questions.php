<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_questions', function (Blueprint $table) {
            $table->decimal('marks', 8, 2)->default(1)->after('order');
            $table->string('image_path')->nullable()->after('marks');
        });
    }

    public function down(): void
    {
        Schema::table('learning_questions', function (Blueprint $table) {
            $table->dropColumn(['marks', 'image_path']);
        });
    }
};