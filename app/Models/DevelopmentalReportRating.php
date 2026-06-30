<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevelopmentalReportRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'developmental_report_id',
        'developmental_skill_id',
        'rating',
    ];

    public function report()
    {
        return $this->belongsTo(DevelopmentalReport::class, 'developmental_report_id');
    }

    public function skill()
    {
        return $this->belongsTo(DevelopmentalSkill::class, 'developmental_skill_id');
    }
}
