<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HealthcareWorker;
use App\Models\KaderAreaAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Flows.md §24 & §26.4: penugasan kader ke wilayah, sumber data
 * `kader_area_assignments` yang dipakai eskalasi alert darurat
 * (EmergencyAlertService). Tidak ada alur eksplisit di Flows.md untuk
 * membuat baris ini — dibangun menyambung §26.4 (admin melihat cakupan)
 * supaya wilayah tanpa kader (§24) bisa langsung ditindaklanjuti di
 * layar yang sama, bukan lewat tinker manual.
 */
class AreaAssignmentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kader_id' => ['required', 'integer', Rule::exists('healthcare_workers', 'id')->where('role', 'kader')->where('status', 'verified')],
            'region_code' => ['required', 'string', 'max:20'],
            'kader_priority' => ['required', Rule::in(['primary', 'secondary'])],
        ]);

        $exists = KaderAreaAssignment::where('kader_id', $data['kader_id'])
            ->where('region_code', $data['region_code'])
            ->exists();
        abort_if($exists, 422, 'Kader ini sudah ditugaskan di wilayah tersebut.');

        KaderAreaAssignment::create($data);
        $kader = HealthcareWorker::find($data['kader_id']);

        return back()->with('success', "{$kader->full_name} ditugaskan ke wilayah {$data['region_code']}.");
    }

    public function destroy(KaderAreaAssignment $areaAssignment): RedirectResponse
    {
        $areaAssignment->delete();

        return back()->with('success', 'Penugasan wilayah dihapus.');
    }
}
