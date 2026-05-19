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
        'gallery_images',
        'video_path',
        'video_url',
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
        'gallery_images' => 'array',
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
        return $this->mediaUrl($this->image_path);
    }

    public function getGalleryImageUrlsAttribute(): array
    {
        return collect($this->gallery_images ?? [])
            ->map(fn ($path) => $this->mediaUrl($path))
            ->filter()
            ->values()
            ->all();
    }

    public function getVideoEmbedUrlAttribute(): ?string
    {
        if (!$this->video_url) {
            return null;
        }

        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/)([^&?/]+)~', $this->video_url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }

        return null;
    }

    public function getVideoFileUrlAttribute(): ?string
    {
        return $this->mediaUrl($this->video_path);
    }

    private function mediaUrl(?string $storedPath): ?string
    {
        if (!$storedPath) {
            return null;
        }

        if (str_starts_with($storedPath, 'http://') || str_starts_with($storedPath, 'https://')) {
            return $storedPath;
        }

        $path = ltrim($storedPath, '/');

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
