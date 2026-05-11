<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'learning_session_id',
        'score',
        'total_questions',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function learningSession()
    {
        return $this->belongsTo(LearningSession::class);
    }

    public function answers()
    {
        return $this->hasMany(LearningAnswer::class);
    }

    public function percentage(): int
    {
        if ($this->total_questions < 1) {
            return 0;
        }

        return (int) round(($this->score / $this->total_questions) * 100);
    }
}
