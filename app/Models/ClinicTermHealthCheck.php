<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicTermHealthCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'recorded_by',
        'term_label',
        'check_type',
        'hostel_name',
        'temperature',
        'pulse',
        'weight_kg',
        'respiration',
        'blood_pressure',
        'health_notes',
        'remark',
        'clearance_status',
        'checked_at',
    ];

    protected $casts = [
        'temperature' => 'decimal:1',
        'pulse' => 'integer',
        'weight_kg' => 'decimal:2',
        'respiration' => 'integer',
        'checked_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
