<?php

use App\Services\OtpGateways\WhatsAppOtpGateway;
use App\Services\OtpService;
use Illuminate\Support\Facades\Http;

it('sends the otp as a WhatsApp Cloud API template message when the gateway is whatsapp', function () {
    config([
        'otp.gateway' => 'whatsapp',
        'otp.whatsapp.phone_number_id' => '1234567890',
        'otp.whatsapp.access_token' => 'test-token',
        'otp.whatsapp.template_name' => 'otp_verification',
        'otp.whatsapp.template_language' => 'id',
    ]);

    Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.test']]], 200)]);

    /** @var OtpService $service */
    $service = $this->app->make(OtpService::class);
    $result = $service->sendCode('081234567890');

    expect($result['debug_code'])->toBeNull();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://graph.facebook.com/v21.0/1234567890/messages'
            && $request['to'] === '+6281234567890'
            && $request['type'] === 'template'
            && $request['template']['name'] === 'otp_verification'
            && $request->hasHeader('Authorization', 'Bearer test-token');
    });
});

it('raises a delivery error when the WhatsApp Cloud API call fails', function () {
    config(['otp.whatsapp.phone_number_id' => '123', 'otp.whatsapp.access_token' => 'bad-token']);
    Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['message' => 'Invalid token']], 401)]);

    $gateway = new WhatsAppOtpGateway;

    expect(fn () => $gateway->send('081234567890', '123456'))->toThrow(RuntimeException::class);
});
