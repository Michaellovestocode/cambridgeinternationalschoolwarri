<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAttendanceMachineEvent extends Model
{
    protected $fillable = [
        'user_id',
        'device_id',
        'enroll_id',
        'event_id',
        'punched_at',
        'direction',
        'raw_payload',
        'attendance_record_id',
    ];

    protected $casts = [
        'punched_at' => 'datetime',
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
