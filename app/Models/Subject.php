<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'category',
        'class_level',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_subject', 'subject_id', 'teacher_id')
                    ->withTimestamps();
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subject', 'subject_id', 'school_class_id')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForClass($query, $classLevel)
    {
        return $query->where(function($q) use ($classLevel) {
            $q->where('class_level', $classLevel)
              ->orWhere('class_level', 'All');
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    // Helper: Get Nigerian grade from score
    public static function getGrade($score)
    {
        if ($score >= 75) return 'A1';
        if ($score >= 70) return 'B2';
        if ($score >= 65) return 'B3';
        if ($score >= 60) return 'C4';
        if ($score >= 55) return 'C5';
        if ($score >= 50) return 'C6';
        if ($score >= 45) return 'D7';
        if ($score >= 40) return 'E8';
        return 'F9';
    }

    // Helper: Get remark from grade
    public static function getRemark($grade)
    {
        $remarks = [
            'A1' => 'EXCELLENT',
            'B2' => 'VERY GOOD',
            'B3' => 'GOOD',
            'C4' => 'CREDIT',
            'C5' => 'CREDIT',
            'C6' => 'CREDIT',
            'D7' => 'PASS',
            'E8' => 'PASS',
            'F9' => 'FAIL',
        ];

        return $remarks[$grade] ?? 'N/A';
    }

    // Helper: Get remark from score directly
    public static function getRemarkFromScore($score)
    {
        $grade = self::getGrade($score);
        return self::getRemark($grade);
    }
}
