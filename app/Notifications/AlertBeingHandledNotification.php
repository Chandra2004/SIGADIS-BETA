<?php

namespace App\Notifications;

use App\Models\EmergencyAlert;
use Illuminate\Notifications\Notification;

class AlertBeingHandledNotification extends Notification
{
    public function __construct(protected EmergencyAlert $alert) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Bantuan sedang menuju lokasi Ibu',
            'body' => ($this->alert->handledBy?->full_name ?? 'Bidan/kader Ibu').' sedang menangani peringatan darurat Ibu.',
            'emergency_alert_id' => $this->alert->id,
        ];
    }
}
