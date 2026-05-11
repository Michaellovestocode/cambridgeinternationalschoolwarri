<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string',
        ]);

        // Check if identifier is email or registration number
        $fieldType = filter_var($credentials['identifier'], FILTER_VALIDATE_EMAIL) ? 'email' : 'registration_number';
        
        $user = User::where($fieldType, $credentials['identifier'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            Auth::login($user);
            $request->session()->regenerate();

            // Redirect based on role
            if ($user->isAdmin() || $user->isTeacher()) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->isParent()) {
                return redirect()->route('parent.dashboard');
            } else {
                return redirect()->route('student.dashboard');
            }
        }

        return back()->withErrors([
            'identifier' => 'The provided credentials do not match our records.',
        ])->onlyInput('identifier');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
