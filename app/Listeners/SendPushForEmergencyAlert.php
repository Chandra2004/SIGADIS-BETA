<?php

namespace App\Listeners;

use App\Contracts\PushNotificationGateway;
use App\Events\EmergencyAlertBroadcast;
use App\Models\DeviceToken;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Push FCM (Architecture.md §5.4) — pelengkap broadcast Reverb, untuk app
 * yang sedang di background/tertutup. Antrean lewat QUEUE_CONNECTION,
 * jalankan `php artisan queue:work` supaya diproses.
 */
class SendPushForEmergencyAlert implements ShouldQueue
{
    public function __construct(protected PushNotificationGateway $gateway) {}

    public function handle(EmergencyAlertBroadcast $event): void
    {
        $tokens = DeviceToken::query()
            ->whereIn('healthcare_worker_id', $event->recipientWorkerIds)
            ->where('is_active', true)
            ->pluck('fcm_token');

        $this->gateway->send(
            $tokens,
            'Peringatan Darurat SIGADIS',
            "Ibu {$event->alert->pregnancy->mother_name} membutuhkan penanganan segera.",
            ['alert_id' => (string) $event->alert->id],
        );
    }
}
