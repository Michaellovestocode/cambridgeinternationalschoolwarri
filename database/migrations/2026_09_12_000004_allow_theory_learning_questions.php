<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('learning_questions', 'correct_option')) {
            Schema::table('learning_questions', function (Blueprint $table) {
                $table->string('correct_option', 1)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Keep this column nullable so existing theory questions remain valid.
    }
};
