<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AdminLoginRequest;
use App\Models\AdminUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;

class AdminAuthController extends Controller
{
    public function showLoginForm(): Response
    {
        return Inertia::render('Admin/Login');
    }

    public function login(AdminLoginRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $limiterKey = 'admin-login:'.$data['email'];

        if (RateLimiter::tooManyAttempts($limiterKey, 5)) {
            $seconds = RateLimiter::availableIn($limiterKey);

            return back()->withErrors(['password' => 'Terlalu banyak percobaan salah, coba lagi dalam '.ceil($seconds / 60).' menit.']);
        }

        $admin = AdminUser::where('email', $data['email'])->first();

        if (! $admin || ! password_verify($data['password'], $admin->password_hash)) {
            RateLimiter::hit($limiterKey, 900);

            return back()->withErrors(['password' => 'Email atau password salah.']);
        }

        RateLimiter::clear($limiterKey);
        Auth::guard('admin')->login($admin, remember: true);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.admin.login.show');
    }
}
