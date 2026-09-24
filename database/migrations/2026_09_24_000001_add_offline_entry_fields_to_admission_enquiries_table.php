<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admission_enquiries', function (Blueprint $table) {
            $table->string('entry_source', 20)->default('online')->after('inquiry_type');
            $table->foreignId('created_by')->nullable()->after('entry_source')->constrained('users')->nullOnDelete();
            $table->index('entry_source');
        });
    }

    public function down(): void
    {
        Schema::table('admission_enquiries', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropIndex(['entry_source']);
            $table->dropColumn(['created_by', 'entry_source']);
        });
    }
};
