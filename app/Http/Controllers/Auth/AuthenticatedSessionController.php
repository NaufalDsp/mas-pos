<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate username (can be email string) and password
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = $credentials['username'];
        $password = $credentials['password'];
        $remember = $request->boolean('remember');

        // Cari user by email, atau username/name jika kolom ada
        $query = User::query();
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $query->where('email', $login);
        } else {
            $added = false;
            if (Schema::hasColumn('users', 'username')) {
                $query->where('username', $login);
                $added = true;
            }
            if (Schema::hasColumn('users', 'name')) {
                if ($added) {
                    $query->orWhere('name', $login);
                } else {
                    $query->where('name', $login);
                    $added = true;
                }
            }
            if (!$added) {
                // Fallback ke email bila kolom username/name tidak ada
                $query->where('email', $login);
            }
        }
        $user = $query->first();

        if (!$user) {
            return back()->withErrors([
                'username' => 'Username yang anda masukkan salah.',
            ])->onlyInput('username');
        }

        if (!Hash::check($password, $user->password)) {
            return back()->withErrors([
                'password' => 'Password yang anda masukkan salah.',
            ])->onlyInput('username');
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();

        // Arahkan ke HomePage setelah login
        return redirect()->intended(route('home'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
