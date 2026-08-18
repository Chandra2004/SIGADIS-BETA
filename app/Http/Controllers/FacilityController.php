<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCurrentPregnancy;
use App\Models\Facility;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Fitur 5 — Info & Bantuan Rujukan Faskes, sisi ibu hamil (PRD.md §4.2).
 * Diurutkan berdasarkan kecocokan region_code; sorting jarak sungguhan
 * (koordinat) butuh lokasi GPS ibu hamil yang belum dikumpulkan sistem.
 */
class FacilityController extends Controller
{
    use ResolvesCurrentPregnancy;

    public function index(): Response
    {
        $pregnancy = $this->currentPregnancy()?->load('activeMidwifeAssignment.midwife');

        $facilities = Facility::query()
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'address', 'phone_number', 'region_code', 'hospital_class', 'has_icu', 'has_nicu', 'nicu_bed_count', 'ambulance_status', 'latitude', 'longitude'])
            ->sortByDesc(fn ($f) => $pregnancy && $f->region_code === $pregnancy->region_code)
            ->values();

        $midwife = $pregnancy?->activeMidwifeAssignment?->midwife;

        return Inertia::render('Mobile/Facilities', [
            'facilities' => $facilities,
            'midwife' => $midwife ? ['full_name' => $midwife->full_name, 'phone_number' => $midwife->phone_number] : null,
        ]);
    }
}
