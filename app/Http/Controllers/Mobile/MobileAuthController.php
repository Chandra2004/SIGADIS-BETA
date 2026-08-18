<?php

namespace App\Http\Controllers\Mobile;

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
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class MobileAuthController extends Controller
{
    public function __construct(protected OtpService $otp) {}

    public function showLoginForm(): Response
    {
        return Inertia::render('Mobile/Auth/Login', [
            'status' => session('status'),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $rawIdentifier = trim($request->input('identifier'));
        $phone = $this->normalizePhone($rawIdentifier);
        $password = $request->input('password');

        // 1. Cek apakah pengguna adalah Bidan / Kader (HealthcareWorker)
        $worker = \App\Models\HealthcareWorker::where('phone_number', $rawIdentifier)
            ->when($phone, fn ($query) => $query->orWhere('phone_number', $phone))
            ->orWhere('str_number', $rawIdentifier)
            ->orWhere('appointment_letter_ref', $rawIdentifier)
            ->first();

        if ($worker && Hash::check($password, $worker->password_hash)) {
            return back()->withErrors([
                'identifier' => 'Akun Bidan dan Kader hanya dapat diakses melalui Portal Website SIGADIS. Silakan masuk melalui peramban/browser di laptop/komputer Anda.',
            ]);
        }

        // 2. Cek apakah pengguna adalah Admin (AdminUser)
        $admin = \App\Models\AdminUser::where('email', $rawIdentifier)->first();
        if ($admin && Hash::check($password, $admin->password_hash)) {
            return back()->withErrors([
                'identifier' => 'Akun Administrator hanya dapat diakses melalui Portal Website SIGADIS.',
            ]);
        }

        // 3. Cek akun Ibu Hamil (PregnantUser)
        $user = PregnantUser::where('phone_number', $phone)->first();

        if (! $user || ! Hash::check($password, $user->password_hash)) {
            throw ValidationException::withMessages([
                'identifier' => 'Nomor Handphone atau kata sandi tidak cocok.',
            ]);
        }

        Auth::guard('pregnant')->login($user, $request->boolean('remember', true));
        $request->session()->regenerate();

        if (blank($user->full_name) && \Illuminate\Support\Facades\Route::has('auth.pregnant.name.show')) {
            return redirect()->route('auth.pregnant.name.show');
        }

        return redirect()->route('mobile.dashboard');
    }

    public function showRegisterForm(): Response
    {
        return Inertia::render('Mobile/Auth/Register');
    }

    public function sendOtp(Request $request): RedirectResponse
    {
        Auth::guard('pregnant')->logout();
        Auth::guard('staff')->logout();

        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'regex:/^(\+62|62|0)?8[1-9][0-9]{7,11}$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'full_name.required' => 'Nama lengkap Bunda wajib diisi.',
            'phone_number.required' => 'Nomor WhatsApp wajib diisi.',
            'phone_number.regex' => 'Format nomor WhatsApp tidak valid (contoh: 081234567890).',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $phone = $this->normalizePhone($request->input('phone_number'));
        $fullName = $request->input('full_name');
        $password = $request->input('password');

        // Simpan sementara data registrasi di sesi
        $request->session()->put("reg_data_{$phone}", [
            'full_name' => $fullName,
            'password_hash' => Hash::make($password),
        ]);

        $limiterKey = "otp-send:{$phone}";

        if (RateLimiter::tooManyAttempts($limiterKey, config('otp.rate_limit_per_10_minutes', 5))) {
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
            ->route('mobile.verify.show', ['phone' => $phone])
            ->with('otp_request_id', $result['otp_request_id'])
            ->with('otp_debug_code', $result['debug_code']);
    }

    public function showVerifyForm(Request $request): Response
    {
        return Inertia::render('Mobile/Auth/VerifyOtp', [
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

        if (! empty($regData['full_name'])) {
            $user->full_name = $regData['full_name'];
        } elseif (empty($user->full_name)) {
            $user->full_name = 'Bunda';
        }

        if (! empty($regData['password_hash'])) {
            $user->password_hash = $regData['password_hash'];
        } elseif (empty($user->password_hash)) {
            $user->password_hash = Hash::make('password123');
        }

        $user->otp_verified_at = now();
        $user->save();

        $request->session()->forget("reg_data_{$data['phone_number']}");

        // Logout dan arahkan pengguna ke halaman Login Mobile dengan notifikasi sukses
        Auth::guard('pregnant')->logout();

        return redirect()->route('mobile.login.show')
            ->with('status', 'Pendaftaran akun Ibu Hamil berhasil! Silakan masuk menggunakan nomor WhatsApp dan kata sandi Anda.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('pregnant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('mobile.login.show');
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

    protected function otpErrorMessage(string $result): string
    {
        return match ($result) {
            'expired' => 'Kode sudah kedaluwarsa, kirim ulang kode.',
            'attempts_exceeded' => 'Terlalu banyak percobaan salah, kirim ulang kode.',
            'invalid_request' => 'Sesi verifikasi tidak valid, ulangi dari awal.',
            default => 'Kode OTP salah, silakan coba lagi.',
        };
    }
}
