<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'category',
        'summary',
        'body',
        'ticker_text',
        'button_label',
        'button_url',
        'image_path',
        'event_date',
        'location',
        'published_at',
        'expires_at',
        'sort_order',
        'is_published',
        'is_pinned',
        'show_in_ticker',
        'send_to_parent_dashboard',
        'parent_messages_sent_at',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_published' => 'boolean',
        'is_pinned' => 'boolean',
        'show_in_ticker' => 'boolean',
        'send_to_parent_dashboard' => 'boolean',
        'parent_messages_sent_at' => 'datetime',
    ];

    public const CATEGORY_ACHIEVEMENT = 'achievement';
    public const CATEGORY_ADMISSION = 'admission';
    public const CATEGORY_EVENT = 'event';
    public const CATEGORY_ANNOUNCEMENT = 'announcement';
    public const CATEGORY_ACADEMIC = 'academic';
    public const CATEGORY_SPORTS = 'sports';

    public static function categories(): array
    {
        return [
            self::CATEGORY_ACHIEVEMENT,
            self::CATEGORY_ADMISSION,
            self::CATEGORY_EVENT,
            self::CATEGORY_ANNOUNCEMENT,
            self::CATEGORY_ACADEMIC,
            self::CATEGORY_SPORTS,
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->where(function (Builder $builder) {
                $builder->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->where(function (Builder $builder) {
                $builder->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            });
    }

    public function scopeHomepageOrder(Builder $query): Builder
    {
        return $query
            ->orderByDesc('is_pinned')
            ->orderBy('sort_order')
            ->orderByDesc('event_date')
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

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, strlen('public/'));
        }

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        if (str_starts_with($path, 'announcements/')) {
            return url('announcement-images/' . $path);
        }

        return url('storage/' . $path);
    }

    public function getDisplayDateAttribute(): ?string
    {
        $date = $this->event_date ?? $this->published_at ?? $this->created_at;

        return $date?->format('F j, Y');
    }

    public function getCategoryLabelAttribute(): string
    {
        return ucfirst($this->category);
    }
}
