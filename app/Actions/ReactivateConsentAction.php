<?php

namespace App\Actions;

use App\Models\Pregnancy;

/**
 * Flows.md §19.2.4: aktifkan kembali bukan meng-undo revoked_at lama —
 * insert row consent baru (versi baru), jejak pencabutan lama tetap ada.
 */
class ReactivateConsentAction
{
    public function handle(Pregnancy $pregnancy, string $consentVersion): void
    {
        $pregnancy->consents()->create([
            'consent_version' => $consentVersion,
            'granted_at' => now(),
        ]);
    }
}
