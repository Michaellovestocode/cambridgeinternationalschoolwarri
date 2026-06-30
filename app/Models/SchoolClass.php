<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    protected $appends = ['display_name', 'section_key', 'section_label', 'level_number'];

    public function students()
    {
        return $this->hasMany(User::class, 'class_id')->where('role', 'student');
    }

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_class', 'school_class_id', 'exam_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'school_class_id', 'subject_id')
                    ->withTimestamps();
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_class', 'school_class_id', 'teacher_id')
                    ->withTimestamps();
    }

    public function formTeacher()
    {
        return $this->hasOne(FormTeacher::class, 'class_id');
    }

    public function activeFormTeacher()
    {
        return $this->hasOne(FormTeacher::class, 'class_id')->where('is_active', true);
    }

    public function developmentalReports()
    {
        return $this->hasMany(DevelopmentalReport::class, 'class_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'class_id');
    }

    public function getDisplayNameAttribute(): string
    {
        $name = trim((string) $this->name);
        $description = trim((string) $this->description);

        return $description !== '' ? "{$name} {$description}" : $name;
    }

    public static function sectionDefinitions(): array
    {
        return [
            'creche' => [
                'label' => 'Early Years',
                'description' => 'Creche, Pre-KG, Nursery, and reception classes',
                'color' => 'from-pink-500 to-rose-500',
                'soft' => 'bg-pink-50 text-pink-700 border-pink-100',
            ],
            'primary' => [
                'label' => 'Primary Section',
                'description' => 'Year 1 to Year 6',
                'color' => 'from-emerald-500 to-teal-600',
                'soft' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
            ],
            'junior_secondary' => [
                'label' => 'Junior Secondary',
                'description' => 'Year 7 to Year 9',
                'color' => 'from-blue-500 to-indigo-600',
                'soft' => 'bg-blue-50 text-blue-700 border-blue-100',
            ],
            'senior_secondary' => [
                'label' => 'Senior Secondary',
                'description' => 'Year 10 to Year 12',
                'color' => 'from-purple-500 to-fuchsia-600',
                'soft' => 'bg-purple-50 text-purple-700 border-purple-100',
            ],
            'other' => [
                'label' => 'Other Classes',
                'description' => 'Classes outside the standard school sections',
                'color' => 'from-slate-500 to-slate-700',
                'soft' => 'bg-slate-50 text-slate-700 border-slate-100',
            ],
        ];
    }

    public function getLevelNumberAttribute(): ?int
    {
        $name = strtolower($this->display_name);

        if (preg_match('/\b(?:year|yr)\s*[-]?\s*(\d{1,2})\b/', $name, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/\bcreche\s*[-]?\s*(\d{1,2})\b/', $name, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    public function getSectionKeyAttribute(): string
    {
        $name = strtolower($this->display_name);
        $level = $this->level_number;

        if (
            str_contains($name, 'creche')
            || str_contains($name, 'nursery')
            || str_contains($name, 'pre kg')
            || str_contains($name, 'pre-kg')
            || str_contains($name, 'pre nursery')
            || str_contains($name, 'pre-nursery')
            || preg_match('/\bkg\b/', $name)
            || str_contains($name, 'kindergarten')
            || str_contains($name, 'reception')
        ) {
            return 'creche';
        }

        if ($level >= 1 && $level <= 6) {
            return 'primary';
        }

        if ($level >= 7 && $level <= 9) {
            return 'junior_secondary';
        }

        if ($level >= 10 && $level <= 12) {
            return 'senior_secondary';
        }

        return 'other';
    }

    public function getSectionLabelAttribute(): string
    {
        return self::sectionDefinitions()[$this->section_key]['label'] ?? 'Other Classes';
    }

    public function reportAuthorityRole(): string
    {
        return in_array($this->section_key, ['junior_secondary', 'senior_secondary'], true)
            ? 'principal'
            : 'head_teacher';
    }

    public function reportAuthorityTitle(): string
    {
        return $this->reportAuthorityRole() === 'principal' ? 'Principal' : 'Head Teacher';
    }

    public function classSortKey(): array
    {
        $sectionOrder = array_flip(array_keys(self::sectionDefinitions()));

        return [
            $sectionOrder[$this->section_key] ?? 99,
            $this->level_number ?? 99,
            strtolower($this->display_name),
        ];
    }
}
