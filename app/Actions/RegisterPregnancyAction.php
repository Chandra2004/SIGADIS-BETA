<?php

namespace App\Actions;

use App\Models\HealthcareWorker;
use App\Models\MidwifeAssignment;
use App\Models\Pregnancy;
use App\Models\PregnantUser;
use Illuminate\Support\Facades\DB;

/**
 * Fitur 1 — Registrasi Kehamilan (PRD.md §4.2, Flows.md §3).
 * Satu transaksi: profil kehamilan + consent + pairing bidan.
 */
class RegisterPregnancyAction
{
    public function handle(PregnantUser $user, array $data): Pregnancy
    {
        return DB::transaction(function () use ($user, $data) {
            $pregnancy = $user->pregnancies()->create([
                'mother_name' => $data['mother_name'],
                'estimated_due_date' => $data['estimated_due_date'] ?? null,
                'hpl_is_estimated' => $data['hpl_is_estimated'],
                'gestational_age_weeks_at_registration' => $data['gestational_age_weeks_at_registration'],
                'is_twin_pregnancy' => $data['is_twin_pregnancy'],
                'has_prior_cesarean' => $data['has_prior_cesarean'],
                'has_gestational_diabetes' => $data['has_gestational_diabetes'],
                'has_chronic_hypertension' => $data['has_chronic_hypertension'],
                'other_medical_conditions' => $data['other_medical_conditions'] ?? [],
                'medical_notes' => $data['medical_notes'] ?? null,
                'region_code' => $data['region_code'],
                'address' => $data['address'] ?? null,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
            ]);

            $pregnancy->consents()->create([
                'consent_version' => $data['consent_version'],
                'granted_at' => now(),
            ]);

            $this->assignMidwife($pregnancy, $data['selected_midwife_id'] ?? null);

            return $pregnancy->fresh(['consents', 'activeMidwifeAssignment.midwife']);
        });
    }

    /**
     * Fail-safe (Rules.md §4.1): kalau tidak ada bidan verified di wilayah,
     * registrasi tetap lanjut tanpa pairing, bukan diblokir (Flows.md §3.4.3).
     */
    protected function assignMidwife(Pregnancy $pregnancy, ?int $selectedMidwifeId): void
    {
        $midwife = $selectedMidwifeId
            ? HealthcareWorker::bidan()->verified()->whereKey($selectedMidwifeId)->first()
            : HealthcareWorker::bidan()->verified()->available()->inRegion($pregnancy->region_code)->first();

        if (! $midwife) {
            return;
        }

        MidwifeAssignment::create([
            'pregnancy_id' => $pregnancy->id,
            'midwife_id' => $midwife->id,
            'assignment_method' => $selectedMidwifeId ? 'manual_pilih' : 'auto_zonasi',
            'is_active' => true,
            'started_at' => now(),
        ]);
    }
}
