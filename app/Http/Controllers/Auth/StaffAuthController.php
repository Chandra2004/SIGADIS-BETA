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
        return Inertia::render('Auth/Login');
    }

    public function login(StaffLoginRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $rawIdentifier = trim($data['identifier'] ?? $data['phone_number'] ?? '');
        $limiterKey = 'staff-login:'.$rawIdentifier;

        if (RateLimiter::tooManyAttempts($limiterKey, 5)) {
            $seconds = RateLimiter::availableIn($limiterKey);

            return back()->withErrors(['password' => 'Terlalu banyak percobaan salah, coba lagi dalam '.ceil($seconds / 60).' menit.']);
        }

        // 1. Cek AdminUser jika identifier berupa email
        if (str_contains($rawIdentifier, '@')) {
            $admin = \App\Models\AdminUser::where('email', $rawIdentifier)->first();
            if ($admin && password_verify($data['password'], $admin->password_hash)) {
                RateLimiter::clear($limiterKey);
                Auth::guard('admin')->login($admin, remember: (bool) $request->boolean('remember', true));
                $request->session()->regenerate();

                return redirect()->route('admin.verifikasi.index');
            }
        }

        // Normalisasi nomor HP jika input berupa angka (8xxx -> 08xxx, 628xxx -> 08xxx)
        $cleanedPhone = preg_replace('/\D+/', '', $rawIdentifier);
        $normalizedPhone = null;
        if (! empty($cleanedPhone)) {
            if (str_starts_with($cleanedPhone, '62')) {
                $normalizedPhone = '0' . substr($cleanedPhone, 2);
            } elseif (str_starts_with($cleanedPhone, '8')) {
                $normalizedPhone = '0' . $cleanedPhone;
            } else {
                $normalizedPhone = $cleanedPhone;
            }
        }

        // 2. Cari di HealthcareWorker (Bidan & Kader via Phone / STR / SK)
        $worker = HealthcareWorker::where('phone_number', $rawIdentifier)
            ->when($normalizedPhone, fn ($query) => $query->orWhere('phone_number', $normalizedPhone))
            ->orWhere('str_number', $rawIdentifier)
            ->orWhere('appointment_letter_ref', $rawIdentifier)
            ->first();

        if ($worker && password_verify($data['password'], $worker->password_hash)) {
            RateLimiter::clear($limiterKey);

            if ($worker->status === 'rejected') {
                return back()->withErrors(['password' => 'Akun Anda ditolak verifikasi admin. Hubungi puskesmas/dinkes wilayah.']);
            }

            Auth::guard('staff')->login($worker, remember: (bool) $request->boolean('remember', true));
            $request->session()->regenerate();

            if ($worker->status === 'pending') {
                return redirect()->route('auth.staff.pending');
            }

            if (\Illuminate\Support\Facades\Route::has('bidan.dashboard')) {
                return redirect()->route('bidan.dashboard');
            }

            return redirect()->route('auth.staff.pending');
        }

        // 3. Cari di PregnantUser (Ibu Hamil via Phone)
        $pregnantUser = \App\Models\PregnantUser::where('phone_number', $rawIdentifier)
            ->when($normalizedPhone, fn ($query) => $query->orWhere('phone_number', $normalizedPhone))
            ->first();

        if ($pregnantUser && password_verify($data['password'], $pregnantUser->password_hash)) {
            RateLimiter::clear($limiterKey);
            Auth::guard('pregnant')->login($pregnantUser, remember: (bool) $request->boolean('remember', true));
            $request->session()->regenerate();

            return redirect()->route('kehamilan.beranda');
        }

        RateLimiter::hit($limiterKey, 900);

        return back()->withErrors(['password' => 'Nomor Handphone / STR / Email atau kata sandi salah.']);
    }

    /**
     * Flows.md §16.4.1: registrasi mandiri, status otomatis "pending"
     * sampai admin puskesmas/dinkes memverifikasi.
     */
    public function showRegisterForm(): Response
    {
        return Inertia::render('Auth/Register');
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
            'region_code' => $data['region_code'] ?? '33.08.05.2009',
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
