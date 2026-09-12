<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'learning_attempt_id',
        'learning_question_id',
        'selected_option',
        'is_correct',
        'teacher_score',
        'teacher_feedback',
        'graded_by',
        'graded_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'teacher_score' => 'decimal:2',
        'graded_at' => 'datetime',
    ];

    public function attempt()
    {
        return $this->belongsTo(LearningAttempt::class, 'learning_attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(LearningQuestion::class, 'learning_question_id');
    }
}
