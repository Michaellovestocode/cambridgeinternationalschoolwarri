<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->boolean('homepage_notice_enabled')->default(true)->after('principal_signature');
            $table->string('homepage_notice_label')->default('Admissions Notice')->after('homepage_notice_enabled');
            $table->string('homepage_notice_text')->default('2026/2027 admission still ongoing')->after('homepage_notice_label');
            $table->string('homepage_notice_url')->default('/apply')->after('homepage_notice_text');
        });
    }

    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn([
                'homepage_notice_enabled',
                'homepage_notice_label',
                'homepage_notice_text',
                'homepage_notice_url',
            ]);
        });
    }
};
