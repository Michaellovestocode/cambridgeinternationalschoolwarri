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

            $score->fill([
                'class_id' => $student->class_id,
                'teacher_id' => $attempt->exam->created_by,
                'ca1' => $score->exists ? $score->ca1 : 0,
                'ca2' => $score->exists ? $score->ca2 : 0,
                'ca3' => 0,
                'exam' => $examScore,
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
        $totalMarks = (float) ($attempt->exam->total_marks ?: 100);
        $score = (float) ($attempt->total_score ?? 0);

        return round(($score / max($totalMarks, 1)) * 60, 2);
    }
}
