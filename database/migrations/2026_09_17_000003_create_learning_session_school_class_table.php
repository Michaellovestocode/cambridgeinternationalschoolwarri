<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_session_school_class', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['learning_session_id', 'school_class_id']);
        });

        DB::table('learning_sessions')
            ->whereNotNull('school_class_id')
            ->orderBy('id')
            ->select(['id', 'school_class_id', 'created_at', 'updated_at'])
            ->each(function ($session) {
                DB::table('learning_session_school_class')->insert([
                    'learning_session_id' => $session->id,
                    'school_class_id' => $session->school_class_id,
                    'created_at' => $session->created_at,
                    'updated_at' => $session->updated_at,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_session_school_class');
    }
};
