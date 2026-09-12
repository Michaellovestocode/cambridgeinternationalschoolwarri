<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearningAttachment extends Model
{
    protected $fillable = [
        'learning_session_id',
        'uploaded_by',
        'name',
        'path',
        'mime_type',
        'size',
    ];

    public function learningSession()
    {
        return $this->belongsTo(LearningSession::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function url(): string
    {
        return asset('storage/' . $this->path);
    }
}
