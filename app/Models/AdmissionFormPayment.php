<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class AdmissionFormPayment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'parent_name',
        'phone',
        'email',
        'student_name',
        'class_level',
        'depositor_name',
        'payment_date',
        'amount_paid',
        'bank_name',
        'payment_reference',
        'payment_notes',
        'status',
        'application_code',
        'approved_at',
        'application_code_used_at',
        'admin_notes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount_paid' => 'decimal:2',
        'approved_at' => 'datetime',
        'application_code_used_at' => 'datetime',
    ];

    public function application(): HasOne
    {
        return $this->hasOne(AdmissionEnquiry::class);
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
        ];
    }

    public static function generateApplicationCode(): string
    {
        do {
            $code = 'CIS-' . now()->format('y') . '-' . Str::upper(Str::random(6));
        } while (self::where('application_code', $code)->exists());

        return $code;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED && filled($this->application_code);
    }
}
