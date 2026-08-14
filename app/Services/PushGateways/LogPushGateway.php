<?php

namespace App\Services\PushGateways;

use App\Contracts\PushNotificationGateway;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Default lokal/testing: tidak kirim push beneran, cuma catat log.
 * Dipakai kalau PUSH_GATEWAY=log atau kredensial Firebase belum diisi.
 */
class LogPushGateway implements PushNotificationGateway
{
    public function send(Collection $tokens, string $title, string $body, array $data = []): void
    {
        Log::info("[Push-lite] \"{$title}\" -> {$body}", ['tokens' => $tokens->all(), 'data' => $data]);
    }
}
