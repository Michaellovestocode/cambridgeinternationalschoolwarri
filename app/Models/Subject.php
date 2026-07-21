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

    public static function gradeScale(): array
    {
        return [
            ['min' => 75, 'max' => 100, 'grade' => 'A1', 'remark' => 'EXCELLENT'],
            ['min' => 70, 'max' => 74, 'grade' => 'B2', 'remark' => 'VERY GOOD'],
            ['min' => 65, 'max' => 69, 'grade' => 'B3', 'remark' => 'GOOD'],
            ['min' => 60, 'max' => 64, 'grade' => 'C4', 'remark' => 'CREDIT'],
            ['min' => 55, 'max' => 59, 'grade' => 'C5', 'remark' => 'CREDIT'],
            ['min' => 50, 'max' => 54, 'grade' => 'C6', 'remark' => 'CREDIT'],
            ['min' => 45, 'max' => 49, 'grade' => 'D7', 'remark' => 'PASS'],
            ['min' => 40, 'max' => 44, 'grade' => 'E8', 'remark' => 'PASS'],
            ['min' => 0, 'max' => 39, 'grade' => 'F9', 'remark' => 'FAIL'],
        ];
    }

    // Helper: Get Nigerian grade from score
    public static function getGrade($score)
    {
        if (! is_numeric($score)) {
            return 'F9';
        }

        $score = (float) $score;
        $scale = collect(self::gradeScale())
            ->sortByDesc('min');

        foreach ($scale as $range) {
            if ($score >= $range['min']) {
                return $range['grade'];
            }
        }

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
