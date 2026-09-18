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
        'assessment_type',
        'assessment_format',
        'is_published',
        'show_answers_to_students',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'show_answers_to_students' => 'boolean',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function targetClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'learning_session_school_class')
            ->orderBy('name');
    }

    public function scopeForSchoolClass($query, int $classId)
    {
        return $query->where(function ($query) use ($classId) {
            $query->where('school_class_id', $classId)
                ->orWhereHas('targetClasses', fn ($classQuery) => $classQuery->whereKey($classId));
        });
    }

    public function isAssignedToClass(?int $classId): bool
    {
        if (! $classId) {
            return false;
        }

        if ((int) $this->school_class_id === $classId) {
            return true;
        }

        if ($this->relationLoaded('targetClasses')) {
            return $this->targetClasses->contains('id', $classId);
        }

        return $this->targetClasses()->whereKey($classId)->exists();
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

    public function attachments()
    {
        return $this->hasMany(LearningAttachment::class)->latest();
    }

    public function comments()
    {
        return $this->hasMany(LearningComment::class)->latest();
    }

    public function feedback()
    {
        return $this->hasMany(LearningFeedback::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
