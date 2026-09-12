<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_comment_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_comment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('read_at');
            $table->timestamps();
            $table->unique(['learning_comment_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_comment_reads');
    }
};
