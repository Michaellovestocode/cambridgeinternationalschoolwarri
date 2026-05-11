<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'learning_session_id',
        'question_text',
        'options',
        'correct_option',
        'explanation',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function learningSession()
    {
        return $this->belongsTo(LearningSession::class);
    }

    public function answers()
    {
        return $this->hasMany(LearningAnswer::class);
    }
}
