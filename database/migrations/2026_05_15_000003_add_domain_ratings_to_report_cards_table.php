<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            if (!Schema::hasColumn('report_cards', 'affective_domain')) {
                $table->json('affective_domain')->nullable()->after('grade_summary');
            }

            if (!Schema::hasColumn('report_cards', 'psychomotor_skills')) {
                $table->json('psychomotor_skills')->nullable()->after('affective_domain');
            }
        });
    }

    public function down(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            if (Schema::hasColumn('report_cards', 'psychomotor_skills')) {
                $table->dropColumn('psychomotor_skills');
            }

            if (Schema::hasColumn('report_cards', 'affective_domain')) {
                $table->dropColumn('affective_domain');
            }
        });
    }
};
