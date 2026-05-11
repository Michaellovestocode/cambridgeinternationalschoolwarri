<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    protected $table = 'academic_sessions';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function terms()
    {
        return $this->hasMany(Term::class);
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    public function reportCards()
    {
        return $this->hasMany(ReportCard::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Methods
    public static function getActive()
    {
        return self::where('is_active', true)->first();
    }

    public function activate()
    {
        // Deactivate all other sessions
        self::where('id', '!=', $this->id)->update(['is_active' => false]);
        
        // Activate this session
        $this->update(['is_active' => true]);
    }
}
