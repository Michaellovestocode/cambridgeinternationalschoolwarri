<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_question_id')->constrained()->cascadeOnDelete();
            $table->string('selected_option', 1)->nullable();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();

            $table->unique(['learning_attempt_id', 'learning_question_id'], 'learning_answer_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_answers');
    }
};
