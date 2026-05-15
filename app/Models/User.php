<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'registration_number',
        'password',
        'role',
        'class_id',
        'photo',
        'date_of_birth',
        'parent_phone_number',
        'whatsapp_number',
        'sex',
        'club_society',
        'favourite_colour',
        'can_manage_blog',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
            'can_manage_blog' => 'boolean',
        ];
    }

    protected $appends = [
        'age',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isParent(): bool
    {
        return $this->role === 'parent';
    }

    public function isBlogManager(): bool
    {
        return $this->role === 'blog_manager';
    }

    public function canManageBlogStudio(): bool
    {
        return $this->isAdmin() || $this->isBlogManager() || (bool) $this->can_manage_blog;
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function examAttempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class, 'created_by');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject', 'teacher_id', 'subject_id')
                    ->withTimestamps();
    }

    public function teachingClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'teacher_class', 'teacher_id', 'school_class_id')
                    ->withTimestamps();
    }

    public function formTeacherAssignments()
    {
        return $this->hasMany(FormTeacher::class, 'teacher_id');
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    public function children()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'parent_id', 'student_id')
            ->withTimestamps()
            ->where('role', 'student');
    }

    public function parents()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id')
            ->withTimestamps()
            ->where('role', 'parent');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class, 'author_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }

    public function getNotificationWhatsappNumberAttribute(): ?string
    {
        return $this->whatsapp_number ?: $this->parent_phone_number;
    }
}
