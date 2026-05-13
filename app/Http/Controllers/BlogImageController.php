<?php

namespace App\Http\Controllers;

class BlogImageController extends Controller
{
    public function show(string $path)
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        if (!str_starts_with($path, 'blog/')) {
            abort(404);
        }

        $baseDirectory = realpath(storage_path('app/public/blog'));
        $requestedFile = realpath(storage_path('app/public/' . $path));

        if (!$baseDirectory || !$requestedFile) {
            abort(404);
        }

        $baseDirectory = str_replace('\\', '/', $baseDirectory);
        $requestedFile = str_replace('\\', '/', $requestedFile);

        if (!str_starts_with($requestedFile, $baseDirectory . '/')) {
            abort(404);
        }

        return response()->file($requestedFile, [
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }
}
