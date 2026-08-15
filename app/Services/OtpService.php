<?php

namespace App\Services;

use App\Contracts\OtpGateway;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Kode OTP disimpan di cache, pengirimannya lewat OtpGateway (WhatsApp
 * Cloud API produksi, log untuk lokal/testing — lihat config/otp.php).
 */
class OtpService
{
    public function __construct(protected OtpGateway $gateway) {}

    public function requestId(string $phoneNumber): string
    {
        return "otp:{$phoneNumber}:request_id";
    }

    public function codeKey(string $phoneNumber): string
    {
        return "otp:{$phoneNumber}:code";
    }

    public function attemptsKey(string $phoneNumber): string
    {
        return "otp:{$phoneNumber}:attempts";
    }

    /**
     * Buat & "kirim" kode OTP baru. Mengembalikan request_id (dipakai
     * frontend untuk mengaitkan langkah verifikasi ke permintaan ini).
     */
    public function sendCode(string $phoneNumber): array
    {
        $code = (string) random_int(0, 10 ** config('otp.length') - 1);
        $code = str_pad($code, config('otp.length'), '0', STR_PAD_LEFT);
        $requestId = (string) Str::uuid();

        $ttl = now()->addMinutes(config('otp.expires_in_minutes'));

        Cache::put($this->codeKey($phoneNumber), $code, $ttl);
        Cache::put($this->requestId($phoneNumber), $requestId, $ttl);
        Cache::forget($this->attemptsKey($phoneNumber));

        $isLiveGateway = (bool) config('otp.status', true) && config('otp.gateway') !== 'log';

        if ($isLiveGateway) {
            try {
                $this->gateway->send($phoneNumber, $code);
            } catch (RuntimeException $e) {
                Log::error("[OTP] Gagal kirim ke {$phoneNumber}: {$e->getMessage()}");

                throw $e;
            }
        } else {
            Log::info("[OTP Mock Mode] Kode OTP untuk {$phoneNumber} adalah: {$code}");
        }

        return [
            'otp_request_id' => $requestId,
            'expires_in_minutes' => config('otp.expires_in_minutes'),
            // Jika OTP_GATEWAY_STATUS=false atau gateway=log, kembalikan debug_code ke UI
            'debug_code' => ! $isLiveGateway ? $code : null,
        ];
    }

    /**
     * @return string "ok" | "invalid_request" | "expired" | "attempts_exceeded" | "wrong_code"
     */
    public function verify(string $phoneNumber, string $requestId, string $code): string
    {
        $storedRequestId = Cache::get($this->requestId($phoneNumber));

        if (! $storedRequestId || $storedRequestId !== $requestId) {
            return 'invalid_request';
        }

        $attempts = (int) Cache::get($this->attemptsKey($phoneNumber), 0);

        if ($attempts >= config('otp.max_verify_attempts')) {
            return 'attempts_exceeded';
        }

        $storedCode = Cache::get($this->codeKey($phoneNumber));

        if (! $storedCode) {
            return 'expired';
        }

        if (! hash_equals($storedCode, $code)) {
            Cache::put($this->attemptsKey($phoneNumber), $attempts + 1, now()->addMinutes(config('otp.expires_in_minutes')));

            return 'wrong_code';
        }

        Cache::forget($this->codeKey($phoneNumber));
        Cache::forget($this->requestId($phoneNumber));
        Cache::forget($this->attemptsKey($phoneNumber));

        return 'ok';
    }
}
