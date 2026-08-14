<?php

namespace App\Actions;

use App\Models\Pregnancy;

/**
 * Flows.md §19.3.3: tidak langsung hard-delete — cuma menandai permintaan.
 * Eksekusi nyata (anonimisasi) dijalankan terjadwal lewat
 * ProcessDataDeletionRequests, supaya alert darurat yang masih terbuka
 * (§19.3.5) tidak terganggu di tengah penanganan.
 */
class RequestDataDeletionAction
{
    public function handle(Pregnancy $pregnancy): void
    {
        $pregnancy->latestConsent?->update(['data_deletion_requested_at' => now()]);
    }
}
