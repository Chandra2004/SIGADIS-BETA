<?php

namespace App\Console\Commands;

use App\Models\Pregnancy;
use Illuminate\Console\Command;

/**
 * Flows.md §19.3.3/§19.3.5 — dijadwalkan lewat routes/console.php. Hanya
 * memproses profil yang permintaan hapusnya belum dieksekusi (belum
 * soft-deleted) dan tidak sedang punya emergency alert terbuka.
 *
 * Cakupan sengaja dibatasi ke identitas per-profil (mother_name) dan
 * soft-delete pregnancy — bukan hard-delete lintas tabel penuh atau opsi
 * "Hapus Akun & Semua Profil" (§19.3.6), itu belum dibangun.
 */
class ProcessDataDeletionRequests extends Command
{
    protected $signature = 'privacy:process-deletions';

    protected $description = 'Anonimkan & soft-delete profil kehamilan yang permintaan hapus datanya sudah bisa diproses';

    public function handle(): int
    {
        $pending = Pregnancy::query()
            ->whereHas('latestConsent', fn ($q) => $q->whereNotNull('data_deletion_requested_at'))
            ->get()
            ->filter(fn (Pregnancy $p) => $p->emergencyAlerts()->open()->doesntExist());

        foreach ($pending as $pregnancy) {
            $pregnancy->update(['mother_name' => 'Data Dihapus']);
            $pregnancy->delete();
        }

        $this->info("Processed {$pending->count()} deletion request(s).");

        return self::SUCCESS;
    }
}
