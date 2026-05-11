<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SchoolSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_name',
        'school_address',
        'school_phone',
        'school_email',
        'school_website',
        'school_logo',
        'school_motto',
        'principal_name',
        'principal_signature',
    ];

    // Get logo URL
    public function getLogoUrl()
    {
        if ($this->school_logo && Storage::disk('public')->exists($this->school_logo)) {
            return Storage::url($this->school_logo);
        }
        
        // Default placeholder logo
        return 'https://ui-avatars.com/api/?name=Cambridge&size=200&background=1E40AF&color=fff&bold=true';
    }

    // Get signature URL
    public function getSignatureUrl()
    {
        if ($this->principal_signature && Storage::disk('public')->exists($this->principal_signature)) {
            return Storage::url($this->principal_signature);
        }
        
        return null;
    }

    // Get or create default settings
    public static function getSettings()
    {
        $settings = self::first();
        
        if (!$settings) {
            $settings = self::create([
                'school_name' => 'Cambridge International School',
                'school_address' => 'Lagos, Nigeria',
                'school_phone' => '+234 XXX XXX XXXX',
                'school_email' => 'info@cambridge.edu.ng',
                'school_motto' => 'Excellence in Education',
                'principal_name' => 'Mr./Mrs. Principal',
            ]);
        }
        
        return $settings;
    }
}
