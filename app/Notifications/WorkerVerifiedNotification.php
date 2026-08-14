<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class WorkerVerifiedNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Akun Anda sudah aktif',
            'body' => 'Verifikasi selesai. Sekarang Anda bisa mengakses dashboard dan menerima tugas pasien.',
        ];
    }
}
