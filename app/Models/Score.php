<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'session_id',
        'term_id',
        'class_id',
        'teacher_id',
        'ca1',
        'ca1_source',
        'ca1_original_cbt_score',
        'ca1_overridden_by',
        'ca1_overridden_at',
        'ca2',
        'ca2_source',
        'ca2_original_cbt_score',
        'ca2_overridden_by',
        'ca2_overridden_at',
        'ca3',
        'ca3_source',
        'ca3_original_cbt_score',
        'ca3_overridden_by',
        'ca3_overridden_at',
        'exam',
        'exam_source',
        'exam_original_cbt_score',
        'exam_overridden_by',
        'exam_overridden_at',
        'total',
        'grade',
        'remark',
        'position',
        'total_students',
        'class_average',
        'status',
        'teacher_comment',
    ];

    protected $casts = [
        'ca1' => 'decimal:2',
        'ca1_original_cbt_score' => 'decimal:2',
        'ca2' => 'decimal:2',
        'ca2_original_cbt_score' => 'decimal:2',
        'ca3' => 'decimal:2',
        'ca3_original_cbt_score' => 'decimal:2',
        'exam' => 'decimal:2',
        'exam_original_cbt_score' => 'decimal:2',
        'total' => 'decimal:2',
        'class_average' => 'decimal:2',
        'ca1_overridden_at' => 'datetime',
        'ca2_overridden_at' => 'datetime',
        'ca3_overridden_at' => 'datetime',
        'exam_overridden_at' => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
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

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Auto-calculate total when saving
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($score) {
            // Calculate total
            $score->total = ($score->ca1 ?? 0) + ($score->ca2 ?? 0) + ($score->exam ?? 0);
            
            // Calculate grade
            $score->grade = Subject::getGrade($score->total);
            
            // Calculate remark
            $score->remark = Subject::getRemark($score->grade);
        });
    }

    // Scopes
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForTerm($query, $sessionId, $termId)
    {
        return $query->where('session_id', $sessionId)
                     ->where('term_id', $termId);
    }

    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Methods
    public function isComplete()
    {
        return $this->ca1 !== null && 
               $this->ca2 !== null && 
               $this->exam !== null;
    }

    public function canEdit()
    {
        return in_array($this->status, ['draft', 'submitted']);
    }

    public function submit()
    {
        if ($this->isComplete()) {
            $this->update(['status' => 'submitted']);
            return true;
        }
        return false;
    }

    public function approve()
    {
        $this->update(['status' => 'approved']);
    }

    public function publish()
    {
        $this->update(['status' => 'published']);
    }

    // Calculate class average for this subject
    public static function calculateClassAverage($subjectId, $classId, $sessionId, $termId)
    {
        return self::where('subject_id', $subjectId)
            ->where('class_id', $classId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId)
            ->where('status', '!=', 'draft')
            ->avg('total');
    }

    // Calculate and update positions for all students in a class
    public static function calculatePositions($subjectId, $classId, $sessionId, $termId)
    {
        $scores = self::where('subject_id', $subjectId)
            ->where('class_id', $classId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId)
            ->orderBy('total', 'desc')
            ->get();

        $totalStudents = $scores->count();
        $lastTotal = null;
        $currentPosition = 0;

        foreach ($scores as $index => $score) {
            if ($lastTotal === null || $score->total < $lastTotal) {
                $currentPosition = $index + 1;
            }

            $score->update([
                'position' => $currentPosition,
                'total_students' => $totalStudents,
            ]);

            $lastTotal = $score->total;
        }
    }
}
