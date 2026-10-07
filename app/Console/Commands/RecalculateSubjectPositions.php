<?php

namespace App\Console\Commands;

use App\Models\ReportCard;
use App\Models\Score;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateSubjectPositions extends Command
{
    protected $signature = 'scores:recalculate-positions
                            {report_card_id : Report card ID to identify the class, session, and term}
                            {--apply : Apply the displayed position-only changes}';

    protected $description = 'Preview or recalculate subject positions from saved, non-draft scores without changing marks.';

    public function handle(): int
    {
        $reportCard = ReportCard::find($this->argument('report_card_id'));
        if (! $reportCard || ! $reportCard->class_id || ! $reportCard->session_id || ! $reportCard->term_id) {
            $this->error('A report card with class, session, and term data is required.');
            return self::FAILURE;
        }

        $classId = (int) $reportCard->class_id;
        $sessionId = (int) $reportCard->session_id;
        $termId = (int) $reportCard->term_id;
        $this->line("Scope: report card #{$reportCard->id}, class {$classId}, session {$sessionId}, term {$termId}.");

        $scores = Score::query()
            ->with(['student:id,name', 'subject:id,name'])
            ->where('class_id', $classId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId)
            ->orderBy('subject_id')
            ->orderByDesc('total')
            ->orderBy('student_id')
            ->get(['id', 'student_id', 'subject_id', 'class_id', 'session_id', 'term_id', 'total', 'position', 'total_students', 'status']);

        if ($scores->isEmpty()) {
            $this->warn('No score records found for that class, session, and term.');
            return self::SUCCESS;
        }

        $overLimitScores = $scores->filter(fn (Score $score) => (float) $score->total > 100);
        if ($overLimitScores->isNotEmpty()) {
            $this->warn('Some saved totals exceed 100. They will be included in ranking but their marks will not be changed:');
            $this->table(['Subject', 'Learner', 'Saved total'], $overLimitScores->map(fn (Score $score) => [
                $score->subject?->name ?? 'Unknown subject',
                $score->student?->name ?? 'Unknown learner',
                number_format((float) $score->total, 2),
            ]));
        }

        $changes = [];
        foreach ($scores->groupBy('subject_id') as $subjectScores) {
            $rankedScores = $subjectScores
                ->filter(fn (Score $score) => $score->status !== 'draft' && (float) $score->total > 0)
                ->values();
            $totalStudents = $rankedScores->count();
            $lastTotal = null;
            $position = 0;

            foreach ($rankedScores as $index => $score) {
                if ($lastTotal === null || (float) $score->total < (float) $lastTotal) {
                    $position = $index + 1;
                }

                if ((int) $score->position !== $position || (int) $score->total_students !== $totalStudents) {
                    $changes[] = [
                        $score->subject?->name ?? 'Unknown subject',
                        $score->student?->name ?? 'Unknown learner',
                        number_format((float) $score->total, 2),
                        $score->position ?? 'Not set',
                        $position,
                    ];
                }
                $lastTotal = $score->total;
            }

            foreach ($subjectScores->filter(fn (Score $score) => $score->status === 'draft' || (float) $score->total <= 0) as $score) {
                if ($score->position !== null || $score->total_students !== null) {
                    $changes[] = [
                        $score->subject?->name ?? 'Unknown subject',
                        $score->student?->name ?? 'Unknown learner',
                        number_format((float) $score->total, 2),
                        $score->position ?? 'Not set',
                        'Clear (not ranked)',
                    ];
                }
            }
        }

        if ($changes === []) {
            $this->info('All subject positions already match the saved scores.');
            return self::SUCCESS;
        }

        $this->table(['Subject', 'Learner', 'Saved total', 'Current position', 'Recalculated position'], $changes);

        if (! $this->option('apply')) {
            $this->warn('Preview only. Add --apply to update position and total_students fields. Scores and marks will not be changed.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($scores, $classId, $sessionId, $termId) {
            foreach ($scores->pluck('subject_id')->unique() as $subjectId) {
                Score::calculatePositions($subjectId, $classId, $sessionId, $termId);
            }
        });

        $this->info('Subject positions recalculated successfully. No scores or marks were changed.');
        return self::SUCCESS;
    }
}