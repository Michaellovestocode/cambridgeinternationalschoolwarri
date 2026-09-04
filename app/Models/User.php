<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'registration_number',
        'attendance_card_uid',
        'attendance_machine_user_id',
        'attendance_section',
        'password',
        'role',
        'class_id',
        'photo',
        'signature',
        'date_of_birth',
        'parent_phone_number',
        'whatsapp_number',
        'sex',
        'club_society',
        'favourite_colour',
        'can_manage_blog',
        'can_manage_attendance',
        'can_review_report_cards',
        'report_authority_role',
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
            'can_manage_attendance' => 'boolean',
            'can_review_report_cards' => 'boolean',
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

    public function isNonTeachingStaff(): bool
    {
        return $this->role === 'non_teaching_staff';
    }

    public function canManageBlogStudio(): bool
    {
        return $this->isAdmin() || $this->isBlogManager() || (bool) $this->can_manage_blog;
    }

    public function canManageAttendance(): bool
    {
        return $this->isAdmin() || (bool) $this->can_manage_attendance;
    }

    public function canReviewReportCards(): bool
    {
        return $this->isAdmin() || ($this->isTeacher() && (bool) $this->can_review_report_cards);
    }

    public function canFillDevelopmentalReports(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (! $this->isTeacher()) {
            return false;
        }

        $assignment = $this->formTeacherAssignments()
            ->where('is_active', true)
            ->with('schoolClass')
            ->first();

        return $assignment && $assignment->schoolClass && in_array(
            $assignment->schoolClass->section_key,
            ['creche', 'other'],
            true
        );
    }

    public function participatesInAttendance(): bool
    {
        return in_array($this->role, ['admin', 'teacher', 'student', 'non_teaching_staff'], true);
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

    public function reportReviewClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'report_card_reviewer_class', 'teacher_id', 'school_class_id')
                    ->withTimestamps();
    }

    public function formTeacherAssignments()
    {
        return $this->hasMany(FormTeacher::class, 'teacher_id');
    }

    public function developmentalReports()
    {
        return $this->hasMany(DevelopmentalReport::class, 'student_id');
    }

    public function isReportAuthority(string $role): bool
    {
        return $this->report_authority_role === $role;
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

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function getNotificationWhatsappNumberAttribute(): ?string
    {
        return $this->whatsapp_number ?: $this->parent_phone_number;
    }
}
