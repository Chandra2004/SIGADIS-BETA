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
        $data = $request->validate(['phone_number' => ['required', 'string', 'max:20']]);
        $limiterKey = 'staff-password-reset:'.$data['phone_number'];

        if (RateLimiter::tooManyAttempts($limiterKey, 3)) {
            $seconds = RateLimiter::availableIn($limiterKey);

            return back()->withErrors(['phone_number' => 'Terlalu banyak percobaan, coba lagi dalam '.ceil($seconds / 60).' menit.']);
        }

        RateLimiter::hit($limiterKey, 600);

        try {
            $result = $this->otp->sendCode($data['phone_number']);
        } catch (RuntimeException) {
            return back()->withErrors(['phone_number' => 'Gagal mengirim kode, coba lagi dalam beberapa saat.']);
        }

        return redirect()
            ->route('auth.staff.password-reset.verify.show', ['phone' => $data['phone_number']])
            ->with('otp_request_id', $result['otp_request_id'])
            ->with('otp_debug_code', $result['debug_code']);
    }

    public function showVerifyForm(Request $request): Response
    {
        return Inertia::render('Desktop/LupaPassword/VerifikasiKode', [
            'phoneNumber' => $request->query('phone', ''),
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
        ]);

        $result = $this->otp->verify($data['phone_number'], $data['otp_request_id'], $data['otp_code']);

        if ($result !== 'ok') {
            return back()->withErrors(['otp_code' => match ($result) {
                'expired' => 'Kode sudah kedaluwarsa, kirim ulang kode.',
                'attempts_exceeded' => 'Terlalu banyak percobaan salah, kirim ulang kode.',
                'invalid_request' => 'Sesi verifikasi tidak valid, ulangi dari input nomor HP.',
                default => 'Kode salah, coba lagi.',
            }]);
        }

        // Sesi verifikasi singkat, cuma buat izinkan layar set password baru
        // sekali pakai — bukan login guard staff (belum tentu nomornya
        // terdaftar sampai langkah reset benar-benar dieksekusi).
        $request->session()->put('password_reset_verified_phone', $data['phone_number']);

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
        $phone = $request->session()->get('password_reset_verified_phone');
        abort_unless($phone, 403);

        $data = $request->validate(['password' => ['required', 'string', 'min:8', 'confirmed']]);

        $worker = HealthcareWorker::where('phone_number', $phone)->first();
        abort_unless($worker, 404, 'Nomor HP tidak terdaftar sebagai akun bidan/kader.');

        $worker->update(['password_hash' => Hash::make($data['password'])]);
        $request->session()->forget('password_reset_verified_phone');

        Auth::guard('staff')->login($worker, remember: true);
        $request->session()->regenerate();

        return redirect()->route('bidan.dashboard')->with('success', 'Password berhasil diubah.');
    }
}
