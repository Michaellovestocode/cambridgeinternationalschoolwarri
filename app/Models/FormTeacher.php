<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormTeacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'class_id',
        'assigned_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'assigned_date' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the teacher who is assigned as form teacher
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the class this form teacher is assigned to
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get all exams for this form teacher's class
     */
    public function classExams()
    {
        return $this->schoolClass->exams();
    }

    /**
     * Get all students in this form teacher's class
     */
    public function classStudents()
    {
        return $this->schoolClass->students();
    }

    /**
     * Get exam attempts for this class
     */
    public function examAttempts()
    {
        return ExamAttempt::whereIn('user_id', $this->classStudents()->pluck('id'))->get();
    }
}
