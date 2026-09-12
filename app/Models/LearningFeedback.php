<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearningFeedback extends Model
{
    protected $table = 'learning_feedback';

    protected $fillable = [
        'learning_session_id',
        'user_id',
        'status',
    ];

    public function learningSession()
    {
        return $this->belongsTo(LearningSession::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
