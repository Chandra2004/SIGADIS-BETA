<?php

namespace App\Events;

use App\Models\EmergencyAlert;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Architecture.md §5.2: update real-time Dashboard Bidan tanpa reload,
 * dikirim ke setiap penerima alert (bidan pendamping + kader wilayah,
 * lihat EmergencyAlertService::assignRecipients).
 */
class EmergencyAlertBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    /** @param  array<int,int>  $recipientWorkerIds */
    public function __construct(
        public EmergencyAlert $alert,
        public array $recipientWorkerIds,
    ) {}

    /** @return array<int,Channel> */
    public function broadcastOn(): array
    {
        return array_map(
            fn (int $workerId) => new PrivateChannel("staff.{$workerId}"),
            $this->recipientWorkerIds,
        );
    }

    public function broadcastAs(): string
    {
        return 'emergency-alert';
    }

    /** @return array<string,mixed> */
    public function broadcastWith(): array
    {
        return [
            'alert_id' => $this->alert->id,
            'pregnancy_id' => $this->alert->pregnancy_id,
            'mother_name' => $this->alert->pregnancy->mother_name,
            'triggered_at' => $this->alert->triggered_at,
        ];
    }
}
