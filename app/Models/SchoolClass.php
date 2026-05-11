<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    protected $appends = ['display_name'];

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
}
