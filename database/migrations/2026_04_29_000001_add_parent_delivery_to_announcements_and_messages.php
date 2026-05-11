<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->boolean('send_to_parent_dashboard')->default(false)->after('show_in_ticker');
            $table->timestamp('parent_messages_sent_at')->nullable()->after('send_to_parent_dashboard');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('announcement_id')
                ->nullable()
                ->after('batch_id')
                ->constrained('announcements')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('announcement_id');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['send_to_parent_dashboard', 'parent_messages_sent_at']);
        });
    }
};
