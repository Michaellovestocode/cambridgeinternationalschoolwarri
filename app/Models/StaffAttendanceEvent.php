<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAttendanceEvent extends Model
{
    protected $fillable = [
        'user_id',
        'machine_id',
        'machine_user_id',
        'event_id',
        'punched_at',
        'direction',
        'payload',
        'attendance_record_id',
    ];

    protected $casts = [
        'punched_at' => 'datetime',
        'payload' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }
}