<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Pregnancy;
use Illuminate\Support\Facades\Auth;

/**
 * Flows.md §16.2: satu nomor HP bisa punya >1 profil kehamilan. Profil
 * "aktif" yang sedang dilihat disimpan di session (switcher §16.2.1),
 * default ke yang paling baru kalau belum pernah memilih.
 */
trait ResolvesCurrentPregnancy
{
    protected function currentPregnancy(): ?Pregnancy
    {
        $user = Auth::guard('pregnant')->user();
        $activeId = session('active_pregnancy_id');

        if ($activeId) {
            $pregnancy = $user->pregnancies()->active()->whereKey($activeId)->first();
            if ($pregnancy) {
                return $pregnancy;
            }
        }

        return $user->pregnancies()->active()->latest()->first();
    }
}
