<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_attempts', function (Blueprint $table) {
            $table->boolean('allow_resubmission')->default(false)->after('is_published');
        });
    }

    public function down(): void
    {
        Schema::table('learning_attempts', function (Blueprint $table) {
            $table->dropColumn('allow_resubmission');
        });
    }
};
