<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportCard extends Model
{
    use HasFactory;

    public const WORKFLOW_DRAFT = 'draft';
    public const WORKFLOW_SUBMITTED = 'submitted_for_review';
    public const WORKFLOW_REJECTED = 'rejected_by_reviewer';
    public const WORKFLOW_ACADEMIC_APPROVED = 'academic_approved';
    public const WORKFLOW_PUBLISHED = 'published';

    protected $fillable = [
        'student_id',
        'session_id',
        'term_id',
        'class_id',
        'subjects',
        'total_score',
        'average_score',
        'position',
        'total_students',
        'overall_grade',
        'grade_summary',
        'affective_domain',
        'psychomotor_skills',
        'days_school_opened',
        'days_present',
        'days_absent',
        'attendance_percentage',
        'class_teacher_comment',
        'class_teacher_name',
        'class_teacher_signature',
        'class_teacher_signature_date',
        'head_teacher_comment',
        'head_teacher_name',
        'head_teacher_signature',
        'head_teacher_signature_date',
        'next_term_begins',
        'overall_remark',
        'teacher_comment',
        'theme_color',
        'pdf_path',
        'word_path',
        'status',
        'workflow_status',
        'review_required',
        'published_at',
        'scores_updated_at',
        'submitted_for_review_at',
        'reviewed_at',
        'reviewed_by',
        'academic_reviewed_by',
        'academic_reviewed_at',
        'academic_rejection_reason',
        'created_by',
    ];

    protected $casts = [
        'subjects' => 'array',
        'total_score' => 'decimal:2',
        'average_score' => 'decimal:2',
        'attendance_percentage' => 'decimal:2',
        'class_teacher_signature_date' => 'date',
        'head_teacher_signature_date' => 'date',
        'next_term_begins' => 'date',
        'grade_summary' => 'array',
        'affective_domain' => 'array',
        'psychomotor_skills' => 'array',
        'review_required' => 'boolean',
        'published_at' => 'datetime',
        'scores_updated_at' => 'datetime',
        'submitted_for_review_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'academic_reviewed_at' => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function academicReviewer()
    {
        return $this->belongsTo(User::class, 'academic_reviewed_by');
    }

    // Get all valid scores for this report
    public function scores()
    {
        return self::studentSubjectScores($this->student_id, $this->session_id, $this->term_id);
    }

    public function getComputedTotalScoreAttribute()
    {
        return (float) $this->scores()->sum('total');
    }

    public function getComputedAverageScoreAttribute()
    {
        $scores = $this->scores();
        $count = $scores->count();

        return $count > 0 ? round($scores->sum('total') / $count, 2) : null;
    }

    public function getComputedOverallGradeAttribute()
    {
        if ($this->computed_average_score === null) {
            return null;
        }

        return \App\Models\Subject::getGrade($this->computed_average_score);
    }

    public static function studentSubjectScores($studentId, $sessionId, $termId)
    {
        return Score::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId)
            ->where('status', '!=', 'draft')
            ->whereNotNull('total')
            ->with('subject')
            ->join('subjects', 'scores.subject_id', '=', 'subjects.id')
            ->select('scores.*')
            ->orderBy('subjects.name')
            ->get();
    }

    public function applyGeneratedSummary(array $summary, array $attributes = []): void
    {
        $this->fill(array_merge($summary, $attributes));

        if (! $this->exists) {
            $this->fill([
                'days_school_opened' => 0,
                'days_present' => 0,
                'days_absent' => 0,
                'attendance_percentage' => 0,
                'next_term_begins' => $attributes['next_term_begins'] ?? null,
            ]);
        }
    }

    // Methods
    public function publish()
    {
        $this->update([
            'status' => 'published',
            'workflow_status' => self::WORKFLOW_PUBLISHED,
            'published_at' => now(),
            'review_required' => false,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);
    }

    public function unpublish()
    {
        $this->update([
            'status' => 'draft',
            'workflow_status' => self::WORKFLOW_ACADEMIC_APPROVED,
            'published_at' => null,
            'review_required' => false,
        ]);
    }

    public function markScoresUpdated(bool $forceReview = true): void
    {
        $attributes = [
            'scores_updated_at' => now(),
        ];

        if ($forceReview || $this->isPublished()) {
            $attributes['status'] = 'generated';
            $attributes['workflow_status'] = self::WORKFLOW_DRAFT;
            $attributes['published_at'] = null;
            $attributes['review_required'] = true;
        }

        $this->forceFill($attributes)->save();
    }

    public function isPublished()
    {
        return $this->status === 'published';
    }

    public function isSubmittedForReview(): bool
    {
        return $this->workflow_status === self::WORKFLOW_SUBMITTED;
    }

    public function isRejectedByReviewer(): bool
    {
        return $this->workflow_status === self::WORKFLOW_REJECTED;
    }

    public function isAcademicallyApproved(): bool
    {
        return in_array($this->workflow_status, [self::WORKFLOW_ACADEMIC_APPROVED, self::WORKFLOW_PUBLISHED], true);
    }

    public function workflowLabel(): string
    {
        return match ($this->workflow_status) {
            self::WORKFLOW_SUBMITTED => 'Submitted for Review',
            self::WORKFLOW_REJECTED => 'Rejected by Reviewer',
            self::WORKFLOW_ACADEMIC_APPROVED => 'Academic Approved',
            self::WORKFLOW_PUBLISHED => 'Published',
            default => 'Draft',
        };
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeeCleared($query)
    {
        return $query->whereExists(function ($subQuery) {
            $subQuery->selectRaw('1')
                ->from('fee_clearances')
                ->whereColumn('fee_clearances.student_id', 'report_cards.student_id')
                ->whereColumn('fee_clearances.session_id', 'report_cards.session_id')
                ->whereColumn('fee_clearances.term_id', 'report_cards.term_id')
                ->where('fee_clearances.is_approved', true);
        });
    }

    public function hasFeeClearance(): bool
    {
        return FeeClearance::isApprovedFor($this->student_id, $this->session_id, $this->term_id);
    }

    // Generate report summary data
    public static function generateForStudent($studentId, $sessionId, $termId)
    {
        $scores = self::studentSubjectScores($studentId, $sessionId, $termId);

        if ($scores->isEmpty()) {
            return null;
        }

        $student = User::find($studentId);
        $totalScore = (float) $scores->sum('total');
        $subjectCount = $scores->count();
        $averageScore = $subjectCount > 0 ? round($totalScore / $subjectCount, 2) : 0;
        $overallGrade = Subject::getGrade($averageScore);

        // Calculate grade distribution
        $gradeSummary = [];
        foreach ($scores as $score) {
            $grade = substr($score->grade, 0, 1); // Get letter only (A, B, C, etc.)
            $gradeSummary[$grade] = ($gradeSummary[$grade] ?? 0) + 1;
        }

        // For report cards we do not include class position in the generated summary
        // to avoid publishing positional data in the performance summary.
        // Position values are required by the report_cards schema, so default to 0
        // when the overall rank is not being calculated here.
        return [
            'total_score' => round($totalScore, 2),
            'average_score' => $averageScore,
            'overall_grade' => $overallGrade,
            'grade_summary' => $gradeSummary,
            'position' => 0,
            'total_students' => 0,
        ];
    }
}
