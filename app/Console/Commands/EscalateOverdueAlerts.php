<?php

namespace App\Console\Commands;

use App\Services\EmergencyAlertService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/** Flows.md §7.3.2 — dijadwalkan lewat routes/console.php. */
class EscalateOverdueAlerts extends Command
{
    protected $signature = 'alerts:escalate';

    protected $description = 'Eskalasi emergency alert yang belum ditangani ke kader secondary wilayah';

    public function handle(EmergencyAlertService $service): int
    {
        $count = $service->escalateOverdue();
        $this->info("Escalated {$count} alert(s).");

        // Heartbeat: ini satu-satunya jalur eskalasi, kalau scheduler mati
        // alert yang tidak ditangani tidak pernah dieskalasi tanpa gejala
        // lain. Nilai ini bisa dipantau ops (mis. alert kalau > 5 menit
        // tanpa update) untuk mendeteksi scheduler berhenti.
        Cache::put('alerts_escalate_last_ran_at', now());

        return self::SUCCESS;
    }
}
