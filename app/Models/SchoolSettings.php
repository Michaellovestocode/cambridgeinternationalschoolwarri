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
        'homepage_notice_enabled',
        'homepage_notice_label',
        'homepage_notice_text',
        'homepage_notice_url',
        'blog_manager_gallery_access_enabled',
    ];

    protected $casts = [
        'homepage_notice_enabled' => 'boolean',
        'blog_manager_gallery_access_enabled' => 'boolean',
    ];

    // Get logo URL
    public function getLogoUrl()
    {
        if ($this->school_logo && Storage::disk('public')->exists($this->school_logo)) {
            return Storage::url($this->school_logo);
        }
        
        return asset('images/schoollogo.jpg');
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
                'school_address' => 'Warri, Delta, Nigeria',
                'school_phone' => '+234 803 289 7744',
                'school_email' => 'info@cambridgeinternationalschoolwarri.com',
                'school_motto' => 'Excellence in Education',
                'principal_name' => 'Mr./Mrs. Principal',
                'homepage_notice_enabled' => true,
                'homepage_notice_label' => 'Admissions Notice',
                'homepage_notice_text' => '2026/2027 admission still ongoing',
                'homepage_notice_url' => '/apply',
                'blog_manager_gallery_access_enabled' => false,
            ]);
        }
        
        return $settings;
    }
}
