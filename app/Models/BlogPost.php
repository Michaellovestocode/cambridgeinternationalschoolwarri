<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'author_id',
        'reviewed_by',
        'title',
        'slug',
        'category',
        'excerpt',
        'body',
        'image_path',
        'status',
        'admin_note',
        'submitted_at',
        'published_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_PENDING,
            self::STATUS_PUBLISHED,
            self::STATUS_REJECTED,
        ];
    }

    public static function categories(): array
    {
        return [
            'education',
            'study tips',
            'parenting',
            'exams',
            'school life',
            'leadership',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopePublicOrder(Builder $query): Builder
    {
        return $query
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        $path = ltrim($this->image_path, '/');

        if (str_starts_with($path, 'blog/')) {
            return url('blog-images/' . $path);
        }

        return asset('storage/' . $path);
    }

    public function getDisplayDateAttribute(): string
    {
        return ($this->published_at ?? $this->created_at)->format('F j, Y');
    }

    public function getReadingMinutesAttribute(): int
    {
        $words = str_word_count(strip_tags($this->body));

        return max(1, (int) ceil($words / 220));
    }

    public function getStatusLabelAttribute(): string
    {
        return Str::headline($this->status);
    }
}
