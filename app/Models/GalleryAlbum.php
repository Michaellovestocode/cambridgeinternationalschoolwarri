<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class GalleryAlbum extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'category',
        'description',
        'cover_image_path',
        'event_date',
        'sort_order',
        'is_published',
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_published' => 'boolean',
    ];

    public static function categories(): array
    {
        return [
            'campus life',
            'classrooms',
            'events',
            'clubs',
            'sports',
            'awards',
            'graduation',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(GalleryImage::class)->orderByDesc('is_featured')->orderBy('sort_order')->oldest();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeHomepageOrder(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderByDesc('event_date')
            ->latest();
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if ($this->cover_image_path) {
            return $this->mediaUrl($this->cover_image_path);
        }

        $featured = $this->images->firstWhere('is_featured', true) ?? $this->images->first();

        return $featured?->image_url;
    }

    public function getDisplayDateAttribute(): ?string
    {
        return $this->event_date?->format('F j, Y');
    }

    public static function uniqueSlug(string $title, ?self $album = null): string
    {
        $base = Str::slug($title) ?: 'gallery-album';
        $slug = $base;
        $counter = 2;

        while (self::where('slug', $slug)
            ->when($album, fn ($query) => $query->whereKeyNot($album->id))
            ->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function mediaUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return url('storage/' . ltrim($path, '/'));
    }
}
