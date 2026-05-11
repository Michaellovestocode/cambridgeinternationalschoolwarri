<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportCard extends Model
{
    use HasFactory;

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
        'published_at',
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
        'published_at' => 'datetime',
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

    // Get all scores for this report
    public function scores()
    {
        return Score::where('student_id', $this->student_id)
            ->where('session_id', $this->session_id)
            ->where('term_id', $this->term_id)
            ->with('subject')
            ->orderBy('subject_id')
            ->get();
    }

    // Methods
    public function publish()
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function unpublish()
    {
        $this->update([
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function isPublished()
    {
        return $this->status === 'published';
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
        $scores = Score::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId)
            ->where('status', '!=', 'draft')
            ->get();

        if ($scores->isEmpty()) {
            return null;
        }

        $student = User::find($studentId);
        $totalScore = $scores->sum('total');
        $averageScore = $scores->avg('total');
        $overallGrade = Subject::getGrade($averageScore);

        // Calculate grade distribution
        $gradeSummary = [];
        foreach ($scores as $score) {
            $grade = substr($score->grade, 0, 1); // Get letter only (A, B, C, etc.)
            $gradeSummary[$grade] = ($gradeSummary[$grade] ?? 0) + 1;
        }

        // Calculate position
        $classId = $student->class_id;
        $allStudents = User::where('class_id', $classId)
            ->where('role', 'student')
            ->get();

        $studentAverages = [];
        foreach ($allStudents as $s) {
            $avg = Score::where('student_id', $s->id)
                ->where('session_id', $sessionId)
                ->where('term_id', $termId)
                ->avg('total');
            
            if ($avg) {
                $studentAverages[$s->id] = $avg;
            }
        }

        arsort($studentAverages);
        $position = array_search($studentId, array_keys($studentAverages)) + 1;
        $totalStudents = count($studentAverages);

        return [
            'total_score' => $totalScore,
            'average_score' => round($averageScore, 2),
            'position' => $position,
            'total_students' => $totalStudents,
            'overall_grade' => $overallGrade,
            'grade_summary' => $gradeSummary,
        ];
    }
}
