<?php

namespace App\Actions;

use App\Models\Pregnancy;

/**
 * Flows.md §19.2: cabut persetujuan untuk satu profil kehamilan. Data lama
 * tetap tersimpan (data-safety-first, Schema.md §1) — cuma memblokir
 * pemrosesan data baru (skrining/risk assessment), bukan menghapus apa pun.
 */
class RevokeConsentAction
{
    public function handle(Pregnancy $pregnancy): void
    {
        $pregnancy->latestConsent?->update(['revoked_at' => now()]);
    }
}
