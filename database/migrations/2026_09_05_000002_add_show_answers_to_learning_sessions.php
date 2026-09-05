<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_sessions', function (Blueprint $table) {
            $table->boolean('show_answers_to_students')->default(false)->after('is_published');
        });
    }

    public function down(): void
    {
        Schema::table('learning_sessions', function (Blueprint $table) {
            $table->dropColumn('show_answers_to_students');
        });
    }
};