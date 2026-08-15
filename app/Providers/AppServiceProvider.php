<?php

namespace App\Providers;

use App\Contracts\OtpGateway;
use App\Contracts\PushNotificationGateway;
use App\Services\OtpGateways\LogOtpGateway;
use App\Services\OtpGateways\WhatsAppOtpGateway;
use App\Services\OtpGateways\WhatsAppWebJsOtpGateway;
use App\Services\PushGateways\FcmPushGateway;
use App\Services\PushGateways\LogPushGateway;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Kreait\Firebase\Contract\Messaging;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OtpGateway::class, fn () => match (config('otp.gateway')) {
            'wwebjs' => new WhatsAppWebJsOtpGateway,
            'whatsapp' => new WhatsAppOtpGateway,
            default => new LogOtpGateway,
        });

        $this->app->bind(PushNotificationGateway::class, fn ($app) => match (config('push.gateway')) {
            'fcm' => new FcmPushGateway($app->make(Messaging::class)),
            default => new LogPushGateway,
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_contains(config('app.url', ''), 'https://')) {
            URL::forceScheme('https');
        }
    }
}
