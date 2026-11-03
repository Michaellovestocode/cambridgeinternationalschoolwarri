<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BirthdayGreeting extends Model
{
    protected $fillable = [
        'student_id',
        'created_by',
        'greeting_date',
        'message',
        'expires_at',
        'guardian_phone',
        'whatsapp_requested',
        'sms_status',
        'sms_sent_at',
        'sms_error',
    ];

    protected function casts(): array
    {
        return [
            'greeting_date' => 'date',
            'expires_at' => 'datetime',
            'whatsapp_requested' => 'boolean',
            'sms_sent_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
