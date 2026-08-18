<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCurrentPregnancy;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Fitur 6 — Riwayat Kehamilan, sisi ibu hamil (PRD.md §4.2, Flows.md §11.3).
 * Riwayat melekat ke pregnancy_id (Schema.md §1 prinsip 1), jadi tetap
 * utuh walau bidan pendamping pernah berganti.
 */
class HistoryController extends Controller
{
    use ResolvesCurrentPregnancy;

    public function index(): Response
    {
        $pregnancy = $this->currentPregnancy();

        if (! $pregnancy) {
            return Inertia::render('Mobile/History', [
                'motherName' => null,
                'screeningSessions' => [],
                'referrals' => [],
            ]);
        }

        return Inertia::render('Mobile/History', [
            'motherName' => $pregnancy->mother_name,
            'screeningSessions' => $pregnancy->screeningSessions()->with('riskAssessment')->latest('started_at')->get(),
            'referrals' => $pregnancy->referrals()->with('facility')->latest('referred_at')->get(),
        ]);
    }
}
