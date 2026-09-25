<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentFeedback extends Model
{
    protected $table = 'parent_feedback';

    protected $fillable = [
        'reference_code',
        'category',
        'is_anonymous',
        'parent_name',
        'email',
        'phone',
        'message',
        'status',
        'admin_notes',
        'reviewed_by',
        'resolved_at',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public const STATUS_NEW = 'new';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_RESOLVED = 'resolved';

    public static function statuses(): array
    {
        return [self::STATUS_NEW, self::STATUS_UNDER_REVIEW, self::STATUS_RESOLVED];
    }

    public static function categories(): array
    {
        return ['complaint', 'suggestion', 'compliment', 'other'];
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
