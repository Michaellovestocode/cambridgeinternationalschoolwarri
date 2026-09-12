<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_attempts', function (Blueprint $table) {
            $table->decimal('score', 8, 2)->default(0)->change();
        });

        Schema::table('learning_answers', function (Blueprint $table) {
            $table->decimal('teacher_score', 8, 2)->nullable()->after('is_correct');
            $table->text('teacher_feedback')->nullable()->after('teacher_score');
            $table->foreignId('graded_by')->nullable()->after('teacher_feedback')->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable()->after('graded_by');
        });

        Schema::table('learning_attempts', function (Blueprint $table) {
            $table->boolean('is_published')->default(false)->after('completed_at');
            $table->foreignId('published_by')->nullable()->after('is_published')->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable()->after('published_by');
        });
    }

    public function down(): void
    {
        Schema::table('learning_answers', function (Blueprint $table) {
            $table->dropForeign(['graded_by']);
            $table->dropColumn(['teacher_score', 'teacher_feedback', 'graded_by', 'graded_at']);
        });

        Schema::table('learning_attempts', function (Blueprint $table) {
            $table->dropForeign(['published_by']);
            $table->dropColumn(['is_published', 'published_by', 'published_at']);
            $table->unsignedSmallInteger('score')->default(0)->change();
        });
    }
};
