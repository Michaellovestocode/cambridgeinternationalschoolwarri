<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'can_review_report_cards')) {
                $table->boolean('can_review_report_cards')->default(false)->after('can_manage_attendance');
            }
        });

        Schema::table('report_cards', function (Blueprint $table) {
            if (! Schema::hasColumn('report_cards', 'workflow_status')) {
                $table->string('workflow_status', 50)->default('draft')->after('status');
            }

            if (! Schema::hasColumn('report_cards', 'submitted_for_review_at')) {
                $table->timestamp('submitted_for_review_at')->nullable()->after('scores_updated_at');
            }

            if (! Schema::hasColumn('report_cards', 'academic_reviewed_by')) {
                $table->foreignId('academic_reviewed_by')->nullable()->after('reviewed_by')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('report_cards', 'academic_reviewed_at')) {
                $table->timestamp('academic_reviewed_at')->nullable()->after('academic_reviewed_by');
            }

            if (! Schema::hasColumn('report_cards', 'academic_rejection_reason')) {
                $table->text('academic_rejection_reason')->nullable()->after('academic_reviewed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            if (Schema::hasColumn('report_cards', 'academic_reviewed_by')) {
                $table->dropConstrainedForeignId('academic_reviewed_by');
            }

            $columns = array_filter([
                Schema::hasColumn('report_cards', 'workflow_status') ? 'workflow_status' : null,
                Schema::hasColumn('report_cards', 'submitted_for_review_at') ? 'submitted_for_review_at' : null,
                Schema::hasColumn('report_cards', 'academic_reviewed_at') ? 'academic_reviewed_at' : null,
                Schema::hasColumn('report_cards', 'academic_rejection_reason') ? 'academic_rejection_reason' : null,
            ]);

            if ($columns) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'can_review_report_cards')) {
                $table->dropColumn('can_review_report_cards');
            }
        });
    }
};
