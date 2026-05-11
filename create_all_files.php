<?php

// Script to generate all Laravel 12 CBT files

$files = [
    // Models
    'app/Models/User.php' => <<<'MODEL'
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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

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

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function examAttempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }
}
MODEL
,

    'app/Models/SchoolClass.php' => <<<'MODEL'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function students()
    {
        return $this->hasMany(User::class, 'class_id')->where('role', 'student');
    }

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_class');
    }
}
MODEL
,

    'app/Models/Exam.php' => <<<'MODEL'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'subject',
        'duration_minutes',
        'total_marks',
        'pass_mark',
        'instructions',
        'created_by',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'exam_class');
    }

    public function attempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function isAvailable(): bool
    {
        return $this->is_active && 
               now()->between($this->start_date, $this->end_date);
    }
}
MODEL
,

    'app/Models/Question.php' => <<<'MODEL'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'question_text',
        'question_type',
        'marks',
        'options',
        'correct_answer',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
        ];
    }

    const TYPE_MULTIPLE_CHOICE = 'multiple_choice';
    const TYPE_THEORY = 'theory';
    const TYPE_CODING = 'coding';
    const TYPE_FILL_BLANK = 'fill_blank';

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function isObjective(): bool
    {
        return in_array($this->question_type, [self::TYPE_MULTIPLE_CHOICE, self::TYPE_FILL_BLANK]);
    }
}
MODEL
,

    'app/Models/ExamAttempt.php' => <<<'MODEL'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exam_id',
        'started_at',
        'submitted_at',
        'time_remaining',
        'status',
        'total_score',
        'objective_score',
        'subjective_score',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_GRADED = 'graded';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class, 'attempt_id');
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isGraded(): bool
    {
        return $this->status === self::STATUS_GRADED;
    }
}
MODEL
,

    'app/Models/Answer.php' => <<<'MODEL'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'attempt_id',
        'question_id',
        'answer_text',
        'is_correct',
        'marks_obtained',
        'graded_by',
        'feedback',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
        ];
    }

    public function attempt()
    {
        return $this->belongsTo(ExamAttempt::class, 'attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function gradedBy()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
}
MODEL
,
];

foreach ($files as $path => $content) {
    $fullPath = __DIR__ . '/' . $path;
    $dir = dirname($fullPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($fullPath, $content);
    echo "Created: $path\n";
}

echo "\nAll models created successfully!\n";
