<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class WorkerRejectedNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Pendaftaran belum bisa disetujui',
            'body' => 'Hubungi puskesmas atau dinas kesehatan wilayah Anda untuk informasi lebih lanjut.',
        ];
    }
}
