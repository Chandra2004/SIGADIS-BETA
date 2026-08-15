<?php

use App\Services\OtpGateways\WhatsAppWebJsOtpGateway;
use App\Services\OtpService;
use Illuminate\Support\Facades\Http;

it('sends the otp via whatsapp-web.js microservice when gateway is wwebjs and status is true', function () {
    config([
        'otp.gateway' => 'wwebjs',
        'otp.status' => true,
        'otp.wwebjs.url' => 'http://127.0.0.1:3000',
    ]);

    Http::fake([
        '127.0.0.1:3000/*' => Http::response(['success' => true], 200),
    ]);

    /** @var OtpService $service */
    $service = $this->app->make(OtpService::class);
    $result = $service->sendCode('085730676143');

    expect($result['debug_code'])->toBeNull();

    Http::assertSent(function ($request) {
        return $request->url() === 'http://127.0.0.1:3000/send-otp'
            && $request['phone'] === '085730676143'
            && isset($request['code'])
            && isset($request['message']);
    });
});

it('throws RuntimeException when whatsapp-web.js microservice is unreachable or fails', function () {
    config([
        'otp.gateway' => 'wwebjs',
        'otp.wwebjs.url' => 'http://127.0.0.1:3000',
    ]);

    Http::fake([
        '127.0.0.1:3000/*' => Http::response(['success' => false, 'error' => 'Service offline'], 503),
    ]);

    $gateway = new WhatsAppWebJsOtpGateway;

    expect(fn () => $gateway->send('085730676143', '123456'))->toThrow(RuntimeException::class);
});
