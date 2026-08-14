<?php

namespace App\Notifications;

use App\Models\Pregnancy;
use Illuminate\Notifications\Notification;

/** Flows.md §6.3 & §29.4.2: pengingat skrining berkala, dapat dimatikan lewat Pengaturan Aplikasi. */
class ScreeningReminderNotification extends Notification
{
    public function __construct(protected Pregnancy $pregnancy) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $isNifas = $this->pregnancy->status === 'nifas';

        return [
            'title' => 'Waktunya skrining berkala',
            'body' => $isNifas
                ? 'Sudah waktunya Ibu melakukan skrining masa nifas lagi.'
                : 'Sudah waktunya Ibu melakukan skrining kehamilan rutin lagi.',
            'pregnancy_id' => $this->pregnancy->id,
            'session_type' => $isNifas ? 'nifas' : 'periodic',
        ];
    }
}
