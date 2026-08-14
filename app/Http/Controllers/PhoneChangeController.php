<?php

namespace App\Http\Controllers;

use App\Models\PregnantUser;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

/**
 * Flows.md §21.1: ganti nomor HP sendiri (beda dari override admin §21.2.3,
 * dipakai saat pengguna KEHILANGAN akses). Di sini pengguna masih login dan
 * masih pegang nomor lama — dua tahap verifikasi: OTP nomor lama dulu (buktikan
 * pemilik akun), baru OTP nomor baru.
 */
class PhoneChangeController extends Controller
{
    protected const PHONE_REGEX = '/^08[0-9]{8,13}$/';

    public function __construct(protected OtpService $otp) {}

    public function show(Request $request): Response
    {
        $user = Auth::guard('pregnant')->user();

        return Inertia::render('Kehamilan/GantiNomorHp', [
            'currentPhoneNumber' => $user->phone_number,
            'oldNumberVerified' => (bool) $request->session()->get('phone_change_old_verified'),
            'oldOtpRequestId' => $request->session()->get('old_otp_request_id'),
            'oldOtpDebugCode' => $request->session()->get('old_otp_debug_code'),
            'newPhoneNumber' => $request->session()->get('new_phone_number'),
            'newOtpRequestId' => $request->session()->get('new_otp_request_id'),
            'newOtpDebugCode' => $request->session()->get('new_otp_debug_code'),
        ]);
    }

    /** Langkah 1: OTP ke nomor lama (yang sedang login), memastikan pemilik akun. */
    public function sendOldNumberOtp(Request $request): RedirectResponse
    {
        $user = Auth::guard('pregnant')->user();

        try {
            $result = $this->otp->sendCode($user->phone_number);
        } catch (RuntimeException) {
            return back()->withErrors(['old_otp_code' => 'Gagal mengirim kode, coba lagi dalam beberapa saat.']);
        }

        return back()
            ->with('old_otp_request_id', $result['otp_request_id'])
            ->with('old_otp_debug_code', $result['debug_code']);
    }

    public function verifyOldNumberOtp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'otp_request_id' => ['required', 'string'],
            'otp_code' => ['required', 'string'],
        ]);

        $user = Auth::guard('pregnant')->user();
        $result = $this->otp->verify($user->phone_number, $data['otp_request_id'], $data['otp_code']);

        if ($result !== 'ok') {
            return back()->withErrors(['old_otp_code' => 'Kode salah atau kedaluwarsa, kirim ulang.']);
        }

        $request->session()->put('phone_change_old_verified', true);

        return back()->with('success', 'Nomor lama terverifikasi. Masukkan nomor HP baru.');
    }

    /** Langkah 2: OTP ke nomor baru. */
    public function sendNewNumberOtp(Request $request): RedirectResponse
    {
        abort_unless($request->session()->get('phone_change_old_verified'), 403, 'Verifikasi nomor lama dulu.');

        $data = $request->validate(['new_phone_number' => ['required', 'string', 'regex:'.self::PHONE_REGEX]]);
        $user = Auth::guard('pregnant')->user();

        if ($data['new_phone_number'] === $user->phone_number) {
            return back()->withErrors(['new_phone_number' => 'Nomor baru harus berbeda dari nomor lama.']);
        }

        // Flows.md §21.1.4: tidak sebutkan detail akun siapa, cukup pesan generik.
        if (PregnantUser::where('phone_number', $data['new_phone_number'])->exists()) {
            return back()->withErrors(['new_phone_number' => 'Nomor ini sudah digunakan akun lain.']);
        }

        try {
            $result = $this->otp->sendCode($data['new_phone_number']);
        } catch (RuntimeException) {
            return back()->withErrors(['new_phone_number' => 'Gagal mengirim kode, coba lagi dalam beberapa saat.']);
        }

        return back()
            ->with('new_phone_number', $data['new_phone_number'])
            ->with('new_otp_request_id', $result['otp_request_id'])
            ->with('new_otp_debug_code', $result['debug_code']);
    }

    public function verifyNewNumberOtp(Request $request): RedirectResponse
    {
        abort_unless($request->session()->get('phone_change_old_verified'), 403, 'Verifikasi nomor lama dulu.');

        $data = $request->validate([
            'new_phone_number' => ['required', 'string', 'regex:'.self::PHONE_REGEX],
            'otp_request_id' => ['required', 'string'],
            'otp_code' => ['required', 'string'],
        ]);

        $result = $this->otp->verify($data['new_phone_number'], $data['otp_request_id'], $data['otp_code']);

        if ($result !== 'ok') {
            return back()->withErrors(['new_otp_code' => 'Kode salah atau kedaluwarsa, kirim ulang.']);
        }

        if (PregnantUser::where('phone_number', $data['new_phone_number'])->exists()) {
            return back()->withErrors(['new_phone_number' => 'Nomor ini sudah digunakan akun lain.']);
        }

        $user = Auth::guard('pregnant')->user();
        $user->update(['phone_number' => $data['new_phone_number']]);
        $request->session()->forget('phone_change_old_verified');

        return redirect()->route('kehamilan.beranda')->with('success', 'Nomor HP berhasil diganti.');
    }
}
