<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'question_passage_id',
        'question_text',
        'question_type',
        'options',
        'correct_answer',
        'marks',
        'order',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
        ];
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function passage()
    {
        return $this->belongsTo(QuestionPassage::class, 'question_passage_id');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function isObjective(): bool
    {
        return in_array($this->question_type, ['multiple_choice', 'fill_blank']);
    }

    // Get full image URL
    public function getImageUrl(): ?string
    {
        if ($this->image_path) {
            return asset('public/' . $this->image_path);
        }
        return null;
    }

    public function formattedQuestionText(): HtmlString
    {
        $text = $this->question_text ?? '';

        if (!preg_match('/<\s*\/?\s*[a-z][^>]*>/i', $text)) {
            return new HtmlString(nl2br(e($text), false));
        }

        $allowedTags = '<u><strong><b><em><i><br><p><div><ol><ul><li><sub><sup>';
        $clean = strip_tags($text, $allowedTags);
        $clean = preg_replace('/<([a-z][a-z0-9]*)(?:\s[^>]*)?>/i', '<$1>', $clean);
        $clean = preg_replace('/<\/([a-z][a-z0-9]*)\s*>/i', '</$1>', $clean);

        return new HtmlString($clean);
    }
}
