<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('learning_questions', 'question_type')) {
            Schema::table('learning_questions', function (Blueprint $table) {
                $table->string('question_type', 20)->default('objective')->after('question_text');
            });
        }

        if (! Schema::hasColumn('learning_questions', 'options')) {
            Schema::table('learning_questions', function (Blueprint $table) {
                $table->json('options')->nullable()->after('question_type');
            });
        }

        if (! Schema::hasColumn('learning_questions', 'correct_option')) {
            Schema::table('learning_questions', function (Blueprint $table) {
                $table->string('correct_option', 1)->nullable()->after('options');
            });
        }

        if (! Schema::hasColumn('learning_questions', 'explanation')) {
            Schema::table('learning_questions', function (Blueprint $table) {
                $table->text('explanation')->nullable()->after('correct_option');
            });
        }

        if (! Schema::hasColumn('learning_questions', 'order')) {
            Schema::table('learning_questions', function (Blueprint $table) {
                $table->unsignedSmallInteger('order')->default(0)->after('explanation');
            });
        }

        if (! Schema::hasColumn('learning_questions', 'marks')) {
            Schema::table('learning_questions', function (Blueprint $table) {
                $table->decimal('marks', 8, 2)->default(1)->after('order');
            });
        }

        if (! Schema::hasColumn('learning_questions', 'image_path')) {
            Schema::table('learning_questions', function (Blueprint $table) {
                $table->string('image_path')->nullable()->after('marks');
            });
        }
    }

    public function down(): void
    {
        // Do not remove conditionally reconciled columns that may contain data.
    }
};