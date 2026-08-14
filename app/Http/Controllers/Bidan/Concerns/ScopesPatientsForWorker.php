<?php

namespace App\Http\Controllers\Bidan\Concerns;

use App\Models\HealthcareWorker;
use App\Models\Pregnancy;
use Illuminate\Database\Eloquent\Builder;

/**
 * Flows.md §10.1: bidan lihat pasien dampingan aktifnya; kader lihat
 * pasien di wilayah kader_area_assignments-nya.
 */
trait ScopesPatientsForWorker
{
    protected function patientsFor(HealthcareWorker $worker): Builder
    {
        if ($worker->role === 'bidan') {
            return Pregnancy::query()->whereHas('activeMidwifeAssignment', fn ($q) => $q->where('midwife_id', $worker->id));
        }

        $regionCodes = $worker->kaderAreaAssignments()->pluck('region_code');

        return Pregnancy::query()->whereIn('region_code', $regionCodes);
    }
}
