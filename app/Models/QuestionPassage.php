<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class QuestionPassage extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'title',
        'body',
        'order',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function formattedBody(): HtmlString
    {
        $text = $this->body ?? '';

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
