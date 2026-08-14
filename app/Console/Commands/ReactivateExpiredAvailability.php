<?php

namespace App\Console\Commands;

use App\Models\HealthcareWorker;
use Illuminate\Console\Command;

/**
 * Flows.md §34.3.4: rentang tanggal nonaktif yang terlewati diaktifkan
 * kembali otomatis. Tanpa batas waktu (unavailable_until null) sengaja
 * tetap nonaktif sampai ditekan manual — pengingat berkala mingguan
 * belum dibangun [USULAN, di luar cakupan sesi ini].
 */
class ReactivateExpiredAvailability extends Command
{
    protected $signature = 'staff:reactivate-expired-availability';

    protected $description = 'Aktifkan kembali bidan/kader yang masa nonaktif sementaranya sudah lewat';

    public function handle(): int
    {
        $count = HealthcareWorker::query()
            ->where('is_available', false)
            ->whereNotNull('unavailable_until')
            ->where('unavailable_until', '<=', now())
            ->update(['is_available' => true, 'unavailable_until' => null]);

        $this->info("Reactivated {$count} worker(s).");

        return self::SUCCESS;
    }
}
