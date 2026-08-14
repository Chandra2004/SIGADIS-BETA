<?php

namespace App\Services\OtpGateways;

use App\Contracts\OtpGateway;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * WhatsApp Cloud API (resmi Meta) — dipilih dibanding SMS gateway karena
 * lebih murah dan tidak butuh registrasi sender ID telco (Architecture.md
 * §5.4 catatan OTP). Kode OTP dikirim lewat template kategori "authentication"
 * (wajib di luar jendela percakapan 24 jam, lihat dokumentasi Cloud API):
 * template harus sudah dibuat & disetujui di Meta Business Manager lebih
 * dulu, nama & bahasanya di-set lewat config('otp.whatsapp.template_name').
 */
class WhatsAppOtpGateway implements OtpGateway
{
    public function send(string $phoneNumber, string $code): void
    {
        $phoneNumberId = config('otp.whatsapp.phone_number_id');
        $accessToken = config('otp.whatsapp.access_token');

        $response = Http::withToken($accessToken)
            ->baseUrl('https://graph.facebook.com/v21.0')
            ->post("/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $this->toE164($phoneNumber),
                'type' => 'template',
                'template' => [
                    'name' => config('otp.whatsapp.template_name'),
                    'language' => ['code' => config('otp.whatsapp.template_language')],
                    'components' => [
                        [
                            'type' => 'body',
                            'parameters' => [['type' => 'text', 'text' => $code]],
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException("Gagal kirim OTP WhatsApp ke {$phoneNumber}: ".$response->body());
        }
    }

    /**
     * Nomor disimpan lokal (08xx), Cloud API butuh E.164. Asumsi nomor
     * Indonesia (+62) — sesuai cakupan proyek (Architecture.md §2).
     */
    protected function toE164(string $phoneNumber): string
    {
        $digits = preg_replace('/\D/', '', $phoneNumber);

        return str_starts_with($digits, '0') ? '+62'.substr($digits, 1) : '+'.$digits;
    }
}
