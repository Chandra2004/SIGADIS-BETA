<?php

namespace App\Http\Controllers\Bidan\Concerns;

use App\Models\Pregnancy;
use Illuminate\Support\Facades\Auth;

/**
 * Aksi yang mengubah data klinis pasien (bukan cuma lihat) — khusus bidan
 * pendamping aktifnya, kader hanya boleh melihat (Flows.md §10.1).
 * Butuh ScopesPatientsForWorker di controller yang sama.
 */
trait AuthorizesPatientManagement
{
    protected function authorizeManage(Pregnancy $pregnancy)
    {
        $worker = Auth::guard('staff')->user();
        abort_unless($worker->role === 'bidan', 403);
        abort_unless($this->patientsFor($worker)->whereKey($pregnancy->id)->exists(), 403);

        return $worker;
    }
}
