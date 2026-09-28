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
        'uniform_due',
        'books_due',
        'hostel_due',
        'lunch_due',
        'enrolment_due',
        'fee_schedule_id',
        'uniform_discount',
        'books_discount',
        'hostel_discount',
        'lunch_discount',
        'enrolment_discount',
        'note',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'uniform_due' => 'decimal:2',
        'books_due' => 'decimal:2',
        'hostel_due' => 'decimal:2',
        'lunch_due' => 'decimal:2',
        'enrolment_due' => 'decimal:2',
        'uniform_discount' => 'decimal:2',
        'books_discount' => 'decimal:2',
        'hostel_discount' => 'decimal:2',
        'lunch_discount' => 'decimal:2',
        'enrolment_discount' => 'decimal:2',
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

    public function payments()
    {
        return $this->hasMany(FeeClearancePayment::class)->orderByDesc('paid_at')->orderByDesc('id');
    }

    public function paidFor(string $category): float
    {
        return (float) $this->payments()->where('category', $category)->sum('amount');
    }

    public function grossTotal(): float
    {
        return round(collect(['uniform', 'books', 'hostel', 'lunch', 'enrolment'])
            ->sum(fn ($category) => (float) $this->{$category . '_due'}), 2);
    }

    public function totalDiscount(): float
    {
        return round(collect(['uniform', 'books', 'hostel', 'lunch', 'enrolment'])
            ->sum(fn ($category) => min((float) $this->{$category . '_due'}, (float) $this->{$category . '_discount'})), 2);
    }

    public function amountPayable(): float
    {
        return round(max(0, $this->grossTotal() - $this->totalDiscount()), 2);
    }

    public function totalPaid(): float
    {
        $installments = $this->relationLoaded('payments')
            ? (float) $this->payments->sum('amount')
            : (float) $this->payments()->sum('amount');

        return round($installments + (float) ($this->amount_paid ?? 0), 2);
    }

    public function balance(): float
    {
        return round(max(0, $this->amountPayable() - $this->totalPaid()), 2);
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
