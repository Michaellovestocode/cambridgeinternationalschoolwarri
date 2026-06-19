<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\ReportCard;
use App\Models\Score;
use App\Models\Session;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Support\Facades\DB;

class CbtReportCardSyncService
{
    public function syncAttempt(ExamAttempt $attempt): ?ReportCard
    {
        $attempt->loadMissing(['exam.subjectModel', 'user']);

        if (!$attempt->isGraded() || !$attempt->exam || !$attempt->user?->class_id) {
            return null;
        }

        $session = Session::getActive();
        $term = Term::getActive();
        $subject = $this->resolveSubject($attempt);

        if (!$session || !$term || !$subject) {
            return null;
        }

        return DB::transaction(function () use ($attempt, $session, $term, $subject) {
            $student = $attempt->user;
            $examScore = $this->normalisedExamScore($attempt);

            $score = Score::firstOrNew([
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'session_id' => $session->id,
                'term_id' => $term->id,
            ]);

            $isTestComponent = $attempt->exam->assessment_component === 'test';
            $isExamComponent = $attempt->exam->assessment_component === 'exam';
            $testWasOverridden = $score->exists && $score->ca1_source === 'cbt_overridden';
            $examWasOverridden = $score->exists && $score->exam_source === 'cbt_overridden';

            $score->fill([
                'class_id' => $student->class_id,
                'teacher_id' => $attempt->exam->created_by,
                'ca1' => $isTestComponent
                    ? ($testWasOverridden ? $score->ca1 : $examScore)
                    : ($score->exists ? $score->ca1 : 0),
                'ca1_source' => $isTestComponent ? ($testWasOverridden ? 'cbt_overridden' : 'cbt') : ($score->exists ? $score->ca1_source : null),
                'ca1_original_cbt_score' => $isTestComponent ? ($testWasOverridden ? $score->ca1_original_cbt_score : null) : ($score->exists ? $score->ca1_original_cbt_score : null),
                'ca1_overridden_by' => $isTestComponent ? ($testWasOverridden ? $score->ca1_overridden_by : null) : ($score->exists ? $score->ca1_overridden_by : null),
                'ca1_overridden_at' => $isTestComponent ? ($testWasOverridden ? $score->ca1_overridden_at : null) : ($score->exists ? $score->ca1_overridden_at : null),
                'ca2' => $score->exists ? $score->ca2 : 0,
                'ca2_source' => $score->exists ? $score->ca2_source : null,
                'ca2_original_cbt_score' => $score->exists ? $score->ca2_original_cbt_score : null,
                'ca2_overridden_by' => $score->exists ? $score->ca2_overridden_by : null,
                'ca2_overridden_at' => $score->exists ? $score->ca2_overridden_at : null,
                'ca3' => 0,
                'ca3_source' => $score->exists ? $score->ca3_source : null,
                'ca3_original_cbt_score' => $score->exists ? $score->ca3_original_cbt_score : null,
                'ca3_overridden_by' => $score->exists ? $score->ca3_overridden_by : null,
                'ca3_overridden_at' => $score->exists ? $score->ca3_overridden_at : null,
                'exam' => $isExamComponent
                    ? ($examWasOverridden ? $score->exam : $examScore)
                    : ($score->exists ? $score->exam : 0),
                'exam_source' => $isExamComponent ? ($examWasOverridden ? 'cbt_overridden' : 'cbt') : ($score->exists ? $score->exam_source : null),
                'exam_original_cbt_score' => $isExamComponent ? ($examWasOverridden ? $score->exam_original_cbt_score : null) : ($score->exists ? $score->exam_original_cbt_score : null),
                'exam_overridden_by' => $isExamComponent ? ($examWasOverridden ? $score->exam_overridden_by : null) : ($score->exists ? $score->exam_overridden_by : null),
                'exam_overridden_at' => $isExamComponent ? ($examWasOverridden ? $score->exam_overridden_at : null) : ($score->exists ? $score->exam_overridden_at : null),
                'status' => 'submitted',
                'teacher_comment' => $score->teacher_comment,
            ]);
            $score->save();

            Score::calculatePositions($subject->id, $student->class_id, $session->id, $term->id);

            $classAverage = Score::calculateClassAverage($subject->id, $student->class_id, $session->id, $term->id);

            Score::where('subject_id', $subject->id)
                ->where('class_id', $student->class_id)
                ->where('session_id', $session->id)
                ->where('term_id', $term->id)
                ->update(['class_average' => $classAverage]);

            $summary = ReportCard::generateForStudent($student->id, $session->id, $term->id);

            if (!$summary) {
                return null;
            }

            $reportCard = ReportCard::firstOrNew([
                'student_id' => $student->id,
                'session_id' => $session->id,
                'term_id' => $term->id,
            ]);

            $reportCard->fill(array_merge($summary, [
                'class_id' => $student->class_id,
                'status' => 'generated',
                'workflow_status' => ReportCard::WORKFLOW_DRAFT,
                'review_required' => true,
                'published_at' => null,
                'scores_updated_at' => now(),
            ]));

            if (!$reportCard->exists) {
                $reportCard->fill([
                    'days_school_opened' => 0,
                    'days_present' => 0,
                    'days_absent' => 0,
                    'attendance_percentage' => 0,
                    'next_term_begins' => $term->next_term_begins,
                ]);
            }

            $reportCard->save();

            return $reportCard;
        });
    }

    private function resolveSubject(ExamAttempt $attempt): ?Subject
    {
        if ($attempt->exam->subjectModel) {
            return $attempt->exam->subjectModel;
        }

        if (!$attempt->exam->subject) {
            return null;
        }

        return Subject::where('name', $attempt->exam->subject)
            ->orWhere('code', $attempt->exam->subject)
            ->first();
    }

    private function normalisedExamScore(ExamAttempt $attempt): float
    {
        $component = $attempt->exam->assessment_component ?? 'exam';
        $targetMax = $component === 'test' ? 30 : 60;
        $totalMarks = (float) ($attempt->exam->total_marks ?: $targetMax);
        $score = (float) ($attempt->total_score ?? 0);

        if ($totalMarks <= 0) {
            return 0.0;
        }

        return round(($score / $totalMarks) * $targetMax, 2);
    }
}
