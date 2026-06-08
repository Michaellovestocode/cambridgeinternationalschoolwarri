<?php

namespace App\Http\Controllers;

use App\Models\SchoolSettings;
use Illuminate\Http\Request;

class GalleryAccessController extends Controller
{
    public function update(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Only admins can change gallery access.');

        $settings = SchoolSettings::getSettings();
        $allowAccess = $request->boolean('blog_manager_gallery_access_enabled');

        $settings->update([
            'blog_manager_gallery_access_enabled' => $allowAccess,
        ]);

        return back()->with(
            'success',
            $allowAccess
                ? 'Blog managers can now access Gallery Manager.'
                : 'Gallery Manager is now restricted to admins.'
        );
    }
}
