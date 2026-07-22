<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Session\TokenMismatchException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        // Add exceptions you don't want reported here
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        // Use the default reporting/exception registration
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $e)
    {
        // Handle CSRF token mismatch with a friendly redirect to login
        if ($e instanceof TokenMismatchException) {
            if ($request->expectsJson() || $request->isJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Session expired. Please login again.'
                ], 419);
            }

            // Redirect guests to login and preserve intended URL so Laravel can redirect back after login
            return redirect()->guest(route('login'))
                ->with('error', 'Session expired — please login again to continue.');
        }

        return parent::render($request, $e);
    }
}
