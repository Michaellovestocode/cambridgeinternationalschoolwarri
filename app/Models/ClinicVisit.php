<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClinicVisit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'recorded_by',
        'visited_at',
        'reason',
        'symptoms',
        'temperature',
        'vital_notes',
        'observation',
        'action_taken',
        'medication_administered',
        'parent_contacted',
        'parent_contacted_at',
        'outcome',
        'notes',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
        'parent_contacted' => 'boolean',
        'parent_contacted_at' => 'datetime',
        'temperature' => 'decimal:1',
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
