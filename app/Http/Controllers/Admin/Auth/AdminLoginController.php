<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminLoginController extends Controller
{
    public function create()
    {
        // Gunakan komponen baru: resources/js/Pages/Auth/LoginAdmin.vue
        return Inertia::render('Auth/LoginAdmin');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Check if user is admin
            if (Auth::user()->role !== 'admin') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'You do not have admin access.',
                ]);
            }

            // Selalu arahkan ke dashboard admin (bukan intended/home)
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin0');
    }
}
