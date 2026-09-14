<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClinicIncident extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id', 'reported_by', 'incident_at', 'incident_type', 'location',
        'description', 'injury_details', 'action_taken', 'referred_to',
        'parent_contacted', 'parent_contacted_at',
    ];

    protected $casts = [
        'incident_at' => 'datetime',
        'parent_contacted' => 'boolean',
        'parent_contacted_at' => 'datetime',
    ];

    public function student() { return $this->belongsTo(User::class, 'student_id'); }
    public function reporter() { return $this->belongsTo(User::class, 'reported_by'); }
}
