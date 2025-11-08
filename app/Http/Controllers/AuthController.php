<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Minimal inline validation fallback: avoid relying on the container's
        // validator binding which may not be present in this partial bootstrap.
        $credentials = $request->only(['username', 'password']);

        $validationErrors = [];
        if (empty($credentials['username']) || !is_string($credentials['username'])) {
            $validationErrors['username'] = 'Username is required and must be a string.';
        }
        if (empty($credentials['password']) || !is_string($credentials['password'])) {
            $validationErrors['password'] = 'Password is required and must be a string.';
        }

        if (!empty($validationErrors)) {
            return back()->withErrors($validationErrors)->onlyInput('username');
        }

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            return redirect('/dashboard')->with('success', 'Login berhasil!');
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login')->with('success', 'Logout berhasil!');
    }
}
