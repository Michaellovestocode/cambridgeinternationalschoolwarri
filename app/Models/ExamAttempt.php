<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exam_id',
        'started_at',
        'submitted_at',
        'time_remaining',
        'status',
        'total_score',
        'objective_score',
        'subjective_score',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_GRADED = 'graded';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class, 'attempt_id');
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isGraded(): bool
    {
        return $this->status === self::STATUS_GRADED;
    }

    public function secondsRemaining(): int
    {
        if (!$this->started_at || !$this->exam) {
            return (int) ($this->time_remaining ?? 0);
        }

        $durationSeconds = (int) $this->exam->duration_minutes * 60;
        $elapsedSeconds = $this->started_at->diffInSeconds(now());

        return max(0, $durationSeconds - $elapsedSeconds);
    }

    public function hasTimeExpired(): bool
    {
        return $this->secondsRemaining() <= 0;
    }
}
