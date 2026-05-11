<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeClearance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'session_id',
        'term_id',
        'is_approved',
        'amount_paid',
        'payment_reference',
        'note',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'amount_paid' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public static function isApprovedFor(int $studentId, int $sessionId, int $termId): bool
    {
        return self::approved()
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId)
            ->exists();
    }
}
