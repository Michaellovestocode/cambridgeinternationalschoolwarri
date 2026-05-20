<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            $table->boolean('review_required')->default(false)->after('status');
            $table->timestamp('scores_updated_at')->nullable()->after('published_at');
            $table->timestamp('reviewed_at')->nullable()->after('scores_updated_at');
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['review_required', 'scores_updated_at', 'reviewed_at']);
        });
    }
};
