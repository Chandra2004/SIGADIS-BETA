<?php

namespace App\Services\PushGateways;

use App\Contracts\PushNotificationGateway;
use Illuminate\Support\Collection;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;

/**
 * Firebase Cloud Messaging (Architecture.md §5.4) — push saat app di
 * background, khusus Emergency Alert. Butuh file service account (env
 * FIREBASE_CREDENTIALS) dari Firebase Console lebih dulu.
 */
class FcmPushGateway implements PushNotificationGateway
{
    public function __construct(protected Messaging $messaging) {}

    public function send(Collection $tokens, string $title, string $body, array $data = []): void
    {
        if ($tokens->isEmpty()) {
            return;
        }

        $message = CloudMessage::new()
            ->withNotification(FcmNotification::create($title, $body))
            ->withData($data);

        $this->messaging->sendMulticast($message, $tokens->all());
    }
}
