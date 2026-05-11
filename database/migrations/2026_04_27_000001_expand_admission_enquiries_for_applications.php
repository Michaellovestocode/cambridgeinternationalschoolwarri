<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admission_enquiries', function (Blueprint $table) {
            $table->string('inquiry_type')->default('enquiry')->after('id');
            $table->string('alternate_phone', 50)->nullable()->after('phone');
            $table->string('student_gender', 20)->nullable()->after('student_name');
            $table->date('student_date_of_birth')->nullable()->after('student_gender');
            $table->string('previous_school')->nullable()->after('class_level');
            $table->string('parent_occupation')->nullable()->after('previous_school');
            $table->text('home_address')->nullable()->after('parent_occupation');
            $table->string('how_heard_about_us')->nullable()->after('home_address');
        });
    }

    public function down(): void
    {
        Schema::table('admission_enquiries', function (Blueprint $table) {
            $table->dropColumn([
                'inquiry_type',
                'alternate_phone',
                'student_gender',
                'student_date_of_birth',
                'previous_school',
                'parent_occupation',
                'home_address',
                'how_heard_about_us',
            ]);
        });
    }
};
