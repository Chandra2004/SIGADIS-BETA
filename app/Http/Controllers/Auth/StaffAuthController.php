<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterStaffRequest;
use App\Http\Requests\Auth\StaffLoginRequest;
use App\Models\HealthcareWorker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Flows.md §9. Verifikasi OTP tambahan di perangkat baru (§9.2) BELUM
 * diimplementasikan di sini — deferred, butuh device fingerprinting.
 * Login sekarang: password saja, lalu redirect sesuai status akun.
 */
class StaffAuthController extends Controller
{
    public function showLoginForm(): Response
    {
        return Inertia::render('Desktop/Login');
    }

    public function login(StaffLoginRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $limiterKey = 'staff-login:'.$data['phone_number'];

        if (RateLimiter::tooManyAttempts($limiterKey, 5)) {
            $seconds = RateLimiter::availableIn($limiterKey);

            return back()->withErrors(['password' => 'Terlalu banyak percobaan salah, coba lagi dalam '.ceil($seconds / 60).' menit.']);
        }

        $worker = HealthcareWorker::where('phone_number', $data['phone_number'])->first();

        if (! $worker || ! password_verify($data['password'], $worker->password_hash)) {
            RateLimiter::hit($limiterKey, 900);

            return back()->withErrors(['password' => 'Nomor HP atau password salah.']);
        }

        RateLimiter::clear($limiterKey);
        Auth::guard('staff')->login($worker, remember: true);
        $request->session()->regenerate();

        if ($worker->status === 'pending') {
            return redirect()->route('auth.staff.pending');
        }

        if ($worker->status === 'rejected') {
            Auth::guard('staff')->logout();

            return back()->withErrors(['password' => 'Akun Anda ditolak verifikasi admin. Hubungi puskesmas/dinkes wilayah.']);
        }

        return redirect()->route('bidan.dashboard');
    }

    /**
     * Flows.md §16.4.1: registrasi mandiri, status otomatis "pending"
     * sampai admin puskesmas/dinkes memverifikasi.
     */
    public function showRegisterForm(): Response
    {
        return Inertia::render('Desktop/Register');
    }

    public function register(RegisterStaffRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $worker = HealthcareWorker::create([
            'full_name' => $data['full_name'],
            'phone_number' => $data['phone_number'],
            'password_hash' => Hash::make($data['password']),
            'role' => $data['role'],
            'str_number' => $data['str_number'] ?? null,
            'appointment_letter_ref' => $data['appointment_letter_ref'] ?? null,
            'region_code' => $data['region_code'],
        ]);

        Auth::guard('staff')->login($worker);
        $request->session()->regenerate();

        return redirect()->route('auth.staff.pending');
    }

    public function pending(): Response
    {
        $worker = Auth::guard('staff')->user();

        return Inertia::render('Desktop/PendingVerification', [
            'worker' => $worker->only('full_name', 'role', 'str_number', 'appointment_letter_ref', 'region_code'),
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('staff')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.staff.login.show');
    }
}
