<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Bidan\Concerns\ScopesPatientsForWorker;
use App\Http\Controllers\Controller;
use App\Models\EmergencyAlert;
use App\Models\Facility;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ReferralController extends Controller
{
    use ScopesPatientsForWorker;

    /** Query ?has_icu=1&has_nicu=1 buat filter faskes (modal Pilih Faskes Rujukan). */
    public function create(Request $request, EmergencyAlert $alert): Response
    {
        $this->authorizeAlert($alert);
        $pregnancy = $alert->pregnancy;

        $query = Facility::query()->where('region_code', $pregnancy->region_code);

        if ($request->boolean('has_icu')) {
            $query->where('has_icu', true);
        }
        if ($request->boolean('has_nicu')) {
            $query->where('has_nicu', true);
        }
        if ($search = trim((string) $request->query('search', ''))) {
            $query->where('name', 'like', "%{$search}%");
        }

        $facilities = $query
            ->get(['id', 'name', 'type', 'address', 'phone_number', 'latitude', 'longitude', 'hospital_class', 'has_icu', 'has_nicu', 'nicu_bed_count', 'ambulance_status'])
            ->map(fn (Facility $f) => [
                ...$f->only('id', 'name', 'type', 'address', 'phone_number', 'hospital_class', 'has_icu', 'has_nicu', 'nicu_bed_count', 'ambulance_status'),
                // Flows.md: jarak lurus dari lokasi GPS alert, bukan rute jalan sungguhan.
                'distance_km' => $alert->latitude && $alert->longitude
                    ? round($f->distanceFromKm((float) $alert->latitude, (float) $alert->longitude), 1)
                    : null,
            ])
            ->sortBy('distance_km', SORT_NUMERIC)
            ->values();

        return Inertia::render('Desktop/Rujukan', [
            'alertId' => $alert->id,
            'motherName' => $pregnancy->mother_name,
            'facilities' => $facilities,
        ]);
    }

    public function store(Request $request, EmergencyAlert $alert): RedirectResponse
    {
        $worker = $this->authorizeAlert($alert);

        $data = $request->validate([
            'facility_id' => ['required', 'integer', 'exists:facilities,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $alert->pregnancy->referrals()->create([
            'emergency_alert_id' => $alert->id,
            'facility_id' => $data['facility_id'],
            'referred_by_id' => $worker->id,
            'referred_at' => now(),
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('bidan.alerts.show', $alert)->with('success', 'Rujukan tercatat.');
    }

    /** Flows.md §10.1: rujukan cuma boleh diproses worker dampingan/wilayah pasien terkait. */
    protected function authorizeAlert(EmergencyAlert $alert)
    {
        $worker = Auth::guard('staff')->user();
        abort_unless($this->patientsFor($worker)->whereKey($alert->pregnancy_id)->exists(), 403);

        return $worker;
    }
}
