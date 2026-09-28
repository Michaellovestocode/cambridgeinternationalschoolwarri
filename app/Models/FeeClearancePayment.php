<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeClearancePayment extends Model
{
    protected $fillable = [
        'fee_clearance_id', 'category', 'amount', 'paid_at', 'payment_reference', 'note', 'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'date',
    ];

    public const CATEGORIES = ['uniform', 'books', 'hostel', 'lunch', 'enrolment'];

    public function clearance()
    {
        return $this->belongsTo(FeeClearance::class, 'fee_clearance_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
