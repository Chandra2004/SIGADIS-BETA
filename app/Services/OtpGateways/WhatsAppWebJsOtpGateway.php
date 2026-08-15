<?php

namespace App\Services\OtpGateways;

use App\Contracts\OtpGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * WhatsApp Web JS Gateway (wwebjs/whatsapp-web.js)
 *
 * Mengirimkan kode OTP WhatsApp melalui microservice Node.js lokal
 * berbasis puppeteer / WhatsApp Web browser app tanpa biaya API Meta.
 */
class WhatsAppWebJsOtpGateway implements OtpGateway
{
    public function send(string $phoneNumber, string $code): void
    {
        $serviceUrl = config('otp.wwebjs.url', 'http://127.0.0.1:3000');
        $endpoint = rtrim($serviceUrl, '/') . '/send-otp';

        $appName = config('app.name', 'SIGADIS');
        $message = "*{$appName} - Sistem Gawat Darurat Ibu-Selamat*\n\n"
            . "Kode verifikasi (OTP) Anda adalah:\n\n"
            . "*{$code}*\n\n"
            . "Kode ini berlaku selama 5 menit. Jangan berikan kode ini kepada siapapun demi keamanan data Anda.";

        try {
            $response = Http::timeout(10)->post($endpoint, [
                'phone' => $phoneNumber,
                'code' => $code,
                'message' => $message,
            ]);

            if ($response->failed()) {
                Log::error("[WhatsApp Web JS] Gagal mengirim pesan ke {$phoneNumber}: " . $response->body());
                throw new RuntimeException("Gagal mengirim OTP WhatsApp via WhatsApp Web JS: " . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error("[WhatsApp Web JS] Connection error to {$endpoint}: " . $e->getMessage());
            throw new RuntimeException("Layanan WhatsApp Web JS belum berjalan di {$endpoint}. Pastikan service Node.js telah aktif: " . $e->getMessage());
        }
    }
}
