<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearningComment extends Model
{
    protected $fillable = [
        'learning_session_id',
        'user_id',
        'parent_id',
        'body',
        'is_pinned',
        'is_hidden',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_hidden' => 'boolean',
    ];

    public function learningSession()
    {
        return $this->belongsTo(LearningSession::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'parent_id')->latest();
    }
}
