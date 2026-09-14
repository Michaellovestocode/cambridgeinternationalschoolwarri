<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicSupplyRequest extends Model
{
    protected $fillable = [
        'requested_by', 'item_name', 'quantity_requested', 'unit', 'reason',
        'status', 'reviewed_by', 'review_notes', 'reviewed_at',
    ];

    protected $casts = [
        'quantity_requested' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    public function requester() { return $this->belongsTo(User::class, 'requested_by'); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
}
