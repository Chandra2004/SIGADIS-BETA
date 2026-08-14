<?php

namespace App\Notifications;

use App\Models\EmergencyAlert;
use Illuminate\Notifications\Notification;

class AlertResolvedNotification extends Notification
{
    public function __construct(protected EmergencyAlert $alert) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Penanganan darurat selesai',
            'body' => 'Peringatan darurat Ibu sudah selesai ditangani. Tetap jaga kesehatan Ibu dan bayi.',
            'emergency_alert_id' => $this->alert->id,
        ];
    }
}
