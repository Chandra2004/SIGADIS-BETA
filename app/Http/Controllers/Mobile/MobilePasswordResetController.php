<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\PregnantUser;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class MobilePasswordResetController extends Controller
{
    public function __construct(protected OtpService $otp) {}

    public function showRequestForm(): Response
    {
        return Inertia::render('Mobile/Auth/LupaPassword/MintaKode');
    }

    public function sendOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'phone_number' => ['required', 'string', 'regex:/^(\+62|62|0)?8[1-9][0-9]{7,11}$/'],
        ], [
            'phone_number.required' => 'Nomor WhatsApp wajib diisi.',
            'phone_number.regex' => 'Format nomor WhatsApp tidak valid.',
        ]);

        $phone = $this->normalizePhone($request->input('phone_number'));

        // Cek apakah akun terdaftar
        $user = PregnantUser::where('phone_number', $phone)->first();
        if (! $user) {
            return back()->withErrors(['phone_number' => 'Nomor WhatsApp tidak terdaftar di sistem.']);
        }

        $limiterKey = "otp-reset:{$phone}";

        if (RateLimiter::tooManyAttempts($limiterKey, 5)) {
            $seconds = RateLimiter::availableIn($limiterKey);

            return back()->withErrors([
                'phone_number' => 'Terlalu banyak permintaan, coba lagi dalam '.ceil($seconds / 60).' menit.',
            ]);
        }

        RateLimiter::hit($limiterKey, 600);

        try {
            $result = $this->otp->sendCode($phone);
        } catch (RuntimeException) {
            return back()->withErrors(['phone_number' => 'Gagal mengirim kode verifikasi, silakan coba lagi.']);
        }

        return redirect()
            ->route('mobile.password-reset.verify.show', ['phone' => $phone])
            ->with('otp_request_id', $result['otp_request_id'])
            ->with('otp_debug_code', $result['debug_code']);
    }

    public function showVerifyForm(Request $request): Response
    {
        return Inertia::render('Mobile/Auth/LupaPassword/VerifikasiKode', [
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
            return back()->withErrors(['otp_code' => 'Kode verifikasi salah atau sudah kedaluwarsa.']);
        }

        // Generate token reset sesi aman
        $resetToken = Str::random(40);
        $request->session()->put("reset_token_{$data['phone_number']}", $resetToken);

        return redirect()->route('mobile.password-reset.form', [
            'phone' => $data['phone_number'],
            'token' => $resetToken,
        ]);
    }

    public function showResetForm(Request $request): Response
    {
        $phone = $request->query('phone', '');
        $token = $request->query('token', '');

        $storedToken = $request->session()->get("reset_token_{$phone}");

        if (! $storedToken || $storedToken !== $token) {
            return redirect()->route('mobile.password-reset.request')
                ->withErrors(['phone_number' => 'Sesi reset kata sandi tidak valid atau telah kedaluwarsa.']);
        }

        return Inertia::render('Mobile/Auth/LupaPassword/AturPassword', [
            'phoneNumber' => $phone,
            'token' => $token,
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'phone_number' => ['required', 'string'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $phone = $request->input('phone_number');
        $token = $request->input('token');

        $storedToken = $request->session()->get("reset_token_{$phone}");

        if (! $storedToken || $storedToken !== $token) {
            return redirect()->route('mobile.password-reset.request')
                ->withErrors(['phone_number' => 'Sesi reset kata sandi tidak valid. Silakan ulangi.']);
        }

        $user = PregnantUser::where('phone_number', $phone)->first();

        if (! $user) {
            return redirect()->route('mobile.password-reset.request')
                ->withErrors(['phone_number' => 'Akun tidak ditemukan.']);
        }

        $user->password_hash = Hash::make($request->input('password'));
        $user->save();

        $request->session()->forget("reset_token_{$phone}");

        return redirect()->route('mobile.login.show')
            ->with('status', 'Kata sandi berhasil diperbarui! Silakan masuk dengan kata sandi baru Bunda.');
    }

    protected function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        $phone = preg_replace('/[^\d]/', '', $phone);

        if (str_starts_with($phone, '62')) {
            return '0'.substr($phone, 2);
        }
        if (str_starts_with($phone, '8')) {
            return '0'.$phone;
        }

        return $phone;
    }
}
