<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('exams', 'assessment_component')) {
            return;
        }

        Schema::table('exams', function (Blueprint $table) {
            $table->string('assessment_component')->default('exam')->after('grading_mode');
        });
    }

    public function down()
    {
        if (!Schema::hasColumn('exams', 'assessment_component')) {
            return;
        }

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('assessment_component');
        });
    }
};
