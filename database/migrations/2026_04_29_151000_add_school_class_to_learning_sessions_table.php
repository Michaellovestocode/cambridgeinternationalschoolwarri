<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_sessions', function (Blueprint $table) {
            $table->foreignId('school_class_id')
                ->nullable()
                ->after('subject_id')
                ->constrained('school_classes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('learning_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_class_id');
        });
    }
};
