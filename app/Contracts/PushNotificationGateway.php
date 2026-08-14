<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface PushNotificationGateway
{
    /**
     * @param  Collection<int,string>  $tokens  FCM device token per penerima.
     * @param  array<string,string>  $data  Payload tambahan (mis. alert_id, pregnancy_id) untuk deep-link di app.
     */
    public function send(Collection $tokens, string $title, string $body, array $data = []): void;
}
