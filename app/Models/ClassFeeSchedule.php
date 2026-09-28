<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassFeeSchedule extends Model
{
    protected $fillable = [
        'class_id', 'session_id', 'term_id', 'uniform_amount', 'books_amount', 'hostel_amount',
        'lunch_amount', 'enrolment_amount', 'updated_by',
    ];

    protected $casts = [
        'uniform_amount' => 'decimal:2',
        'books_amount' => 'decimal:2',
        'hostel_amount' => 'decimal:2',
        'lunch_amount' => 'decimal:2',
        'enrolment_amount' => 'decimal:2',
    ];
}
