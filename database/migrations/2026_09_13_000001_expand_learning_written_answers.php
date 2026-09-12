<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('learning_answers', 'selected_option')) {
            Schema::table('learning_answers', function (Blueprint $table) {
                $table->text('selected_option')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Keep written answers intact rather than truncating them back to one character.
    }
};
