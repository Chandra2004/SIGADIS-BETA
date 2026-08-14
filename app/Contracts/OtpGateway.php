<?php

namespace App\Contracts;

interface OtpGateway
{
    /**
     * Kirim kode OTP ke nomor tujuan. Implementasi harus melempar exception
     * kalau pengiriman gagal, supaya OtpService bisa memutuskan retry/gagal
     * dengan jelas daripada diam-diam gagal.
     */
    public function send(string $phoneNumber, string $code): void;
}
