<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearningCommentRead extends Model
{
    protected $fillable = [
        'learning_comment_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function comment()
    {
        return $this->belongsTo(LearningComment::class, 'learning_comment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
