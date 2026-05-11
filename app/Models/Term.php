<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'name',
        'term_number',
        'start_date',
        'end_date',
        'next_term_begins',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'next_term_begins' => 'date',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function session()
    {
        return $this->belongsTo(Session::class);
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
        // Deactivate all other terms in this session
        self::where('session_id', $this->session_id)
            ->where('id', '!=', $this->id)
            ->update(['is_active' => false]);
        
        // Activate this term
        $this->update(['is_active' => true]);
    }

    public function getFullNameAttribute()
    {
        return $this->session->name . ' - ' . $this->name;
    }
}
