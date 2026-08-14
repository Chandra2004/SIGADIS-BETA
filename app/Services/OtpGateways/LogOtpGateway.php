<?php

namespace App\Services\OtpGateways;

use App\Contracts\OtpGateway;
use Illuminate\Support\Facades\Log;

/**
 * Default lokal/testing: tidak kirim apa-apa keluar, cuma catat log.
 * Dipakai kalau OTP_GATEWAY=log atau kredensial WhatsApp belum diisi.
 */
class LogOtpGateway implements OtpGateway
{
    public function send(string $phoneNumber, string $code): void
    {
        Log::info("[OTP-lite] Kode untuk {$phoneNumber}: {$code}");
    }
}
