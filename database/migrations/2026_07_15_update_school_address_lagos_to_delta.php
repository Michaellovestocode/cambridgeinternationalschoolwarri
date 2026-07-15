<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('school_settings')
            ->where('school_address', 'LIKE', '%Lagos%')
            ->update(['school_address' => 'Delta, Nigeria']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('school_settings')
            ->where('school_address', 'Delta, Nigeria')
            ->update(['school_address' => 'Lagos, Nigeria']);
    }
};
