<?php

namespace App\Http\Middleware;

use App\Models\SchoolSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GalleryStudioMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        abort_unless(
            $user->canManageBlogStudio()
                && SchoolSettings::getSettings()->blog_manager_gallery_access_enabled,
            403,
            'Gallery access is currently restricted to admins.'
        );

        return $next($request);
    }
}
