<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\HealthcareWorker;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

/**
 * Lupa password bidan/kader, lewat WhatsApp OTP (bukan email/SMS) — pakai
 * OtpService yang sama dengan OTP ibu hamil (config/otp.php, gateway WA).
 */
class StaffPasswordResetController extends Controller
{
    public function __construct(protected OtpService $otp) {}

    public function showRequestForm(): Response
    {
        return Inertia::render('Desktop/LupaPassword/MintaKode');
    }

    /**
     * Anti-enumerasi sama seperti PregnantAuthController::sendOtp — respons
     * sama baik nomor terdaftar atau tidak, supaya penyerang tidak bisa
     * menebak nomor HP bidan/kader mana yang punya akun.
     */
    public function sendOtp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone_number' => ['required', 'string', 'max:255'],
        ], [
            'phone_number.required' => 'Nomor WhatsApp atau Email wajib diisi.',
        ]);

        $raw = trim($data['phone_number']);
        $isEmail = str_contains($raw, '@');
        $phone = $isEmail ? $raw : $this->normalizePhone($raw);
        $limiterKey = 'staff-password-reset:'.$phone;

        if (RateLimiter::tooManyAttempts($limiterKey, 5)) {
            $seconds = RateLimiter::availableIn($limiterKey);

            return back()->withErrors(['phone_number' => 'Terlalu banyak percobaan, coba lagi dalam '.ceil($seconds / 60).' menit.']);
        }

        RateLimiter::hit($limiterKey, 600);

        try {
            $result = $this->otp->sendCode($phone);
        } catch (RuntimeException) {
            return back()->withErrors(['phone_number' => 'Gagal mengirim kode, coba lagi dalam beberapa saat.']);
        }

        return redirect()
            ->route('auth.staff.password-reset.verify.show', ['phone' => $phone])
            ->with('otp_request_id', $result['otp_request_id'])
            ->with('otp_debug_code', $result['debug_code']);
    }

    public function showVerifyForm(Request $request): Response
    {
        $raw = trim($request->query('phone', ''));
        $phone = str_contains($raw, '@') ? $raw : $this->normalizePhone($raw);

        return Inertia::render('Desktop/LupaPassword/VerifikasiKode', [
            'phoneNumber' => $phone,
            'otpRequestId' => $request->session()->get('otp_request_id'),
            'debugCode' => $request->session()->get('otp_debug_code'),
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone_number' => ['required', 'string'],
            'otp_request_id' => ['required', 'string'],
            'otp_code' => ['required', 'string'],
        ], [
            'otp_code.required' => 'Kode OTP verifikasi wajib diisi.',
        ]);

        $raw = trim($data['phone_number']);
        $phone = str_contains($raw, '@') ? $raw : $this->normalizePhone($raw);
        $result = $this->otp->verify($phone, $data['otp_request_id'], $data['otp_code']);

        if ($result !== 'ok') {
            return back()->withErrors(['otp_code' => match ($result) {
                'expired' => 'Kode sudah kedaluwarsa, kirim ulang kode.',
                'attempts_exceeded' => 'Terlalu banyak percobaan salah, kirim ulang kode.',
                'invalid_request' => 'Sesi verifikasi tidak valid, ulangi dari input nomor HP/Email.',
                default => 'Kode salah, coba lagi.',
            }]);
        }

        // Simpan sesi terverifikasi untuk proses input password baru
        $request->session()->put('password_reset_verified_phone', $phone);

        return redirect()->route('auth.staff.password-reset.form');
    }

    public function showResetForm(Request $request): Response|RedirectResponse
    {
        if (! $request->session()->get('password_reset_verified_phone')) {
            return redirect()->route('auth.staff.password-reset.request');
        }

        return Inertia::render('Desktop/LupaPassword/AturPassword');
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $identifier = $request->session()->get('password_reset_verified_phone');
        if (! $identifier) {
            return redirect()->route('auth.staff.password-reset.request')->withErrors([
                'phone_number' => 'Sesi reset kata sandi telah berakhir. Silakan minta kode verifikasi baru.',
            ]);
        }

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $newPasswordHash = Hash::make($data['password']);

        // 1. Cek AdminUser jika identifier berupa email
        if (str_contains($identifier, '@')) {
            $admin = \App\Models\AdminUser::where('email', $identifier)->first();
            if ($admin) {
                $admin->password_hash = $newPasswordHash;
                $admin->save();

                $request->session()->forget('password_reset_verified_phone');

                return redirect()->route('auth.staff.login.show')->with('status', 'Kata sandi akun Administrator berhasil diperbarui! Silakan masuk dengan kata sandi baru Anda.');
            }
        }

        $normalized = $this->normalizePhone($identifier);
        $altPhone = str_starts_with($normalized, '0') ? substr($normalized, 1) : '0' . $normalized;

        // 2. Cek HealthcareWorker (Bidan & Kader)
        $worker = HealthcareWorker::where('phone_number', $normalized)
            ->orWhere('phone_number', $altPhone)
            ->first();

        if ($worker) {
            $worker->password_hash = $newPasswordHash;
            $worker->save();

            $request->session()->forget('password_reset_verified_phone');

            return redirect()->route('auth.staff.login.show')->with('status', 'Kata sandi tenaga kesehatan berhasil diperbarui! Silakan masuk dengan kata sandi baru Anda.');
        }

        // 3. Cek PregnantUser (Ibu Hamil)
        $pregnantUser = \App\Models\PregnantUser::where('phone_number', $normalized)
            ->orWhere('phone_number', $altPhone)
            ->first();

        if ($pregnantUser) {
            $pregnantUser->password_hash = $newPasswordHash;
            $pregnantUser->save();

            $request->session()->forget('password_reset_verified_phone');

            return redirect()->route('auth.staff.login.show')->with('status', 'Kata sandi akun Ibu Hamil berhasil diperbarui! Silakan masuk dengan kata sandi baru Anda.');
        }

        return back()->withErrors(['password' => 'Akun dengan nomor / email ini tidak ditemukan di sistem.']);
    }

    protected function normalizePhone(string $phone): string
    {
        $cleaned = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($cleaned, '62')) {
            return '0' . substr($cleaned, 2);
        }
        if (str_starts_with($cleaned, '8')) {
            return '0' . $cleaned;
        }

        return $cleaned;
    }
}
