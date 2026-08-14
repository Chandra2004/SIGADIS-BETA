<?php

namespace App\Console\Commands;

use App\Models\Pregnancy;
use App\Notifications\ScreeningReminderNotification;
use Illuminate\Console\Command;

/**
 * Flows.md §6.3: pengingat skrining berkala mendekati jadwal berikutnya.
 * Kehamilan yang belum pernah menyelesaikan skrining awal tidak diingatkan
 * di sini (itu bukan siklus berkala) — hanya yang sudah punya sesi selesai.
 * Idempoten lewat tabel notifications sendiri (bukan kolom baru): tidak
 * kirim ulang selama belum ada sesi selesai baru sejak reminder terakhir.
 */
class SendScreeningReminders extends Command
{
    protected $signature = 'screening:send-reminders';

    protected $description = 'Kirim notifikasi pengingat skrining berkala untuk ibu hamil yang sudah jatuh tempo';

    public function handle(): int
    {
        $intervalDays = (int) config('screening.reminder_interval_days');
        $sent = 0;

        Pregnancy::active()
            ->whereHas('pregnantUser', fn ($q) => $q->where('screening_reminder_enabled', true))
            ->with('pregnantUser')
            ->chunkById(100, function ($pregnancies) use ($intervalDays, &$sent) {
                foreach ($pregnancies as $pregnancy) {
                    if (! $pregnancy->hasActiveConsent()) {
                        continue;
                    }

                    if ($pregnancy->screeningSessions()->where('is_complete', false)->exists()) {
                        continue;
                    }

                    $lastCompleted = $pregnancy->screeningSessions()
                        ->where('is_complete', true)
                        ->latest('completed_at')
                        ->first();

                    if (! $lastCompleted || $lastCompleted->completed_at->gt(now()->subDays($intervalDays))) {
                        continue;
                    }

                    $alreadyReminded = $pregnancy->pregnantUser->notifications()
                        ->where('type', ScreeningReminderNotification::class)
                        ->where('created_at', '>=', $lastCompleted->completed_at)
                        ->exists();

                    if ($alreadyReminded) {
                        continue;
                    }

                    $pregnancy->pregnantUser->notify(new ScreeningReminderNotification($pregnancy));
                    $sent++;
                }
            });

        $this->info("Sent {$sent} screening reminder(s).");

        return self::SUCCESS;
    }
}
