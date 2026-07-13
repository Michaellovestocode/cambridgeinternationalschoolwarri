<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevelopmentalReport extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'student_id',
        'class_id',
        'session_id',
        'term_id',
        'form_teacher_id',
        'authority_id',
        'authority_role',
        'days_school_opened',
        'days_present',
        'days_absent',
        'attendance_percentage',
        'class_teacher_remark',
        'authority_remark',
        'form_teacher_name',
        'form_teacher_signature',
        'form_teacher_signed_at',
        'authority_name',
        'authority_signature',
        'authority_signed_at',
        'status',
        'submitted_at',
        'published_at',
        'published_by',
    ];

    protected function casts(): array
    {
        return [
            'form_teacher_signed_at' => 'date',
            'authority_signed_at' => 'date',
            'submitted_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public static function ratingLabels(): array
    {
        return [
            'Q0' => 'Demonstration and Communication',
            'Q1' => 'Beginning',
            'Q2' => 'Developing',
            'Q3' => 'Very Good',
            'Q4' => 'Proficient',
        ];
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function formTeacher()
    {
        return $this->belongsTo(User::class, 'form_teacher_id');
    }

    public function authority()
    {
        return $this->belongsTo(User::class, 'authority_id');
    }

    public function publisher()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function ratings()
    {
        return $this->hasMany(DevelopmentalReportRating::class);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function authorityTitle(): string
    {
        return $this->authority_role === 'principal' ? 'Principal' : 'Head Teacher';
    }
}
