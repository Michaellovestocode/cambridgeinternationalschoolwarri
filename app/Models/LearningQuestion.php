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
        'question_type',
        'options',
        'correct_option',
        'explanation',
        'order',
        'marks',
        'image_path',
    ];

    protected $casts = [
        'options' => 'array',
        'marks' => 'decimal:2',
    ];

    public function learningSession()
    {
        return $this->belongsTo(LearningSession::class);
    }

    public function answers()
    {
        return $this->hasMany(LearningAnswer::class);
    }

    public function getImageUrl(): ?string
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }

        return null;
    }
}
