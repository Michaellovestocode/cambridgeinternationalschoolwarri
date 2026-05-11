<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'school_class_id',
        'created_by',
        'title',
        'topic',
        'description',
        'lesson_content',
        'learning_goals',
        'estimated_minutes',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->hasMany(LearningQuestion::class)->orderBy('order')->orderBy('id');
    }

    public function attempts()
    {
        return $this->hasMany(LearningAttempt::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
