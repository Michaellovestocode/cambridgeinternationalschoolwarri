<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'club_society')) {
                $table->string('club_society')->nullable()->after('sex');
            }

            if (!Schema::hasColumn('users', 'favourite_colour')) {
                $table->string('favourite_colour')->nullable()->after('club_society');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'favourite_colour')) {
                $table->dropColumn('favourite_colour');
            }

            if (Schema::hasColumn('users', 'club_society')) {
                $table->dropColumn('club_society');
            }
        });
    }
};
