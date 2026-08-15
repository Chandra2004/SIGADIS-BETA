<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OTP Settings
    |--------------------------------------------------------------------------
    |
    | Kanal pengiriman OTP: WhatsApp Cloud API (Architecture.md §5.4), bukan
    | SMS — lebih murah dan tidak butuh registrasi sender ID telco. Default
    | "log" (catat ke log aplikasi, kode dibalikin di response) dipakai untuk
    | lokal/testing/staging sebelum kredensial WhatsApp Business tersedia.
    |
    */

    'length' => 6,

    'expires_in_minutes' => 5,

    'max_verify_attempts' => 5,

    'resend_cooldown_seconds' => 60,

    'rate_limit_per_10_minutes' => 3,

    /*
    |--------------------------------------------------------------------------
    | OTP Gateway Status (Testing vs Real WhatsApp)
    |--------------------------------------------------------------------------
    | true  : Kirim pesan asli ke WhatsApp (via wwebjs / Meta Cloud API)
    | false : Mode simulasi / dev (OTP tidak dikirim ke WA, kode OTP
    |         ditampilkan langsung di layar UI web di atas kotak input OTP)
    |
    */
    'status' => filter_var(env('OTP_GATEWAY_STATUS', true), FILTER_VALIDATE_BOOLEAN),

    'gateway' => env('OTP_GATEWAY', 'log'), // 'log' | 'wwebjs' | 'whatsapp'

    'wwebjs' => [
        'url' => env('WWEBJS_URL', 'http://127.0.0.1:3000'),
    ],

    'whatsapp' => [
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
        'template_name' => env('WHATSAPP_OTP_TEMPLATE', 'otp_verification'),
        'template_language' => env('WHATSAPP_OTP_TEMPLATE_LANGUAGE', 'id'),
    ],

];
