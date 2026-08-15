<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\PregnantUser;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class PregnantAuthController extends Controller
{
    public function __construct(protected OtpService $otp) {}

    public function showPhoneForm(): Response
    {
        return Inertia::render('Auth/Phone');
    }

    /**
     * Fitur 1 (PRD.md): satu alur untuk akun baru maupun lama — selalu
     * kirim OTP, tidak pernah membalas "nomor belum terdaftar" (mencegah
     * enumerasi akun, Flows.md §2.1.4).
     */
    public function sendOtp(SendOtpRequest $request): RedirectResponse
    {
        Auth::guard('pregnant')->logout();
        Auth::guard('staff')->logout();

        $phone = $request->validated('phone_number');
        $fullName = $request->input('full_name');
        $password = $request->input('password');

        if (! empty($fullName) || ! empty($password)) {
            $request->session()->put("reg_data_{$phone}", [
                'full_name' => $fullName,
                'password_hash' => ! empty($password) ? Hash::make($password) : null,
            ]);
        }

        $limiterKey = "otp-send:{$phone}";

        if (RateLimiter::tooManyAttempts($limiterKey, config('otp.rate_limit_per_10_minutes'))) {
            $seconds = RateLimiter::availableIn($limiterKey);

            return back()->withErrors([
                'phone_number' => 'Terlalu banyak percobaan, coba lagi dalam '.ceil($seconds / 60).' menit.',
            ]);
        }

        RateLimiter::hit($limiterKey, 600);

        try {
            $result = $this->otp->sendCode($phone);
        } catch (RuntimeException) {
            return back()->withErrors(['phone_number' => 'Gagal mengirim kode OTP, coba lagi dalam beberapa saat.']);
        }

        return redirect()
            ->route('auth.pregnant.verify.show', ['phone' => $phone])
            ->with('otp_request_id', $result['otp_request_id'])
            ->with('otp_debug_code', $result['debug_code']);
    }

    public function showVerifyForm(Request $request): Response
    {
        return Inertia::render('Auth/VerifyOtp', [
            'phoneNumber' => $request->query('phone', ''),
            'otpRequestId' => $request->session()->get('otp_request_id'),
            'debugCode' => $request->session()->get('otp_debug_code'),
        ]);
    }

    public function verifyOtp(VerifyOtpRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $result = $this->otp->verify($data['phone_number'], $data['otp_request_id'], $data['otp_code']);

        if ($result !== 'ok') {
            return back()->withErrors(['otp_code' => $this->otpErrorMessage($result)]);
        }

        $regData = $request->session()->get("reg_data_{$data['phone_number']}");
        $user = PregnantUser::firstOrNew(['phone_number' => $data['phone_number']]);
        $isNewAccount = ! $user->exists;

        if (! empty($regData['full_name'])) {
            $user->full_name = $regData['full_name'];
        } elseif ($isNewAccount && empty($user->full_name)) {
            $user->full_name = '';
        }

        if (! empty($regData['password_hash'])) {
            $user->password_hash = $regData['password_hash'];
        } elseif ($isNewAccount && empty($user->password_hash)) {
            $user->password_hash = Hash::make('password123');
        }

        $user->otp_verified_at = now();
        $user->save();

        $request->session()->forget("reg_data_{$data['phone_number']}");

        // Jika pendaftaran via form web (ada data registrasi stashed di session),
        // arahkan ke halaman Login dengan notifikasi sukses sesuai instruksi alur
        if (! empty($regData)) {
            return redirect()->route('auth.staff.login.show')->with('status', 'Pendaftaran akun Ibu Hamil berhasil! Silakan masuk menggunakan nomor WhatsApp dan kata sandi Anda.');
        }

        Auth::guard('pregnant')->login($user, remember: true);
        $request->session()->regenerate();

        if ($isNewAccount && blank($user->full_name)) {
            return redirect()->route('auth.pregnant.name.show');
        }

        return redirect()->route('kehamilan.beranda');
    }

    protected function otpErrorMessage(string $result): string
    {
        return match ($result) {
            'expired' => 'Kode sudah kedaluwarsa, kirim ulang kode.',
            'attempts_exceeded' => 'Terlalu banyak percobaan salah, kirim ulang kode.',
            'invalid_request' => 'Sesi verifikasi tidak valid, ulangi dari input nomor HP.',
            default => 'Kode salah, coba lagi.',
        };
    }

    public function showNameForm(): Response
    {
        return Inertia::render('Auth/Name');
    }

    public function saveName(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
        ]);

        /** @var \App\Models\PregnantUser $user */
        $user = Auth::guard('pregnant')->user();
        $user->update(['full_name' => $data['full_name']]);

        return redirect()->route('kehamilan.registrasi.show');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('pregnant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.pregnant.phone.show');
    }
}
