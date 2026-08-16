<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk Manajemen Fasilitas Kesehatan & Rujukan (Point 4).
 * Mengelola direktori faskes, kontak darurat/IGD, titik koordinat GPS,
 * dan kapasitas layanan maternal (ICU, NICU, Ambulans Siaga).
 */
class AdminFacilityController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'type' => $request->query('type', 'semua'),
            'search' => trim((string) $request->query('search', '')),
        ];

        $facilities = Facility::query()
            ->when($filters['type'] !== 'semua', fn ($q) => $q->where('type', $filters['type']))
            ->when($filters['search'] !== '', fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name', 'like', "%{$filters['search']}%")
                ->orWhere('address', 'like', "%{$filters['search']}%")
                ->orWhere('region_code', 'like', "%{$filters['search']}%")
                ->orWhere('phone_number', 'like', "%{$filters['search']}%")))
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        // Metrik Ringkasan Faskes
        $totalFacilities = Facility::count();
        $hospitalCount = Facility::where('type', 'rumah_sakit')->count();
        $puskesmasCount = Facility::whereIn('type', ['puskesmas', 'pustu', 'polindes'])->count();
        $nicuBedTotal = Facility::sum('nicu_bed_count');
        $ambulanceReadyCount = Facility::where('ambulance_status', 'siaga')->count();

        return Inertia::render('Admin/Fasilitas', [
            'facilities' => $facilities,
            'filters' => $filters,
            'metrics' => [
                'total' => $totalFacilities,
                'hospitals' => $hospitalCount,
                'puskesmas' => $puskesmasCount,
                'nicu_beds' => $nicuBedTotal,
                'ambulance_ready' => $ambulanceReadyCount,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::in(['puskesmas', 'pustu', 'polindes', 'rumah_sakit', 'klinik'])],
            'region_code' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'hospital_class' => ['nullable', 'string', 'max:10'],
            'has_icu' => ['boolean'],
            'has_nicu' => ['boolean'],
            'nicu_bed_count' => ['nullable', 'integer', 'min:0'],
            'ambulance_status' => ['required', Rule::in(['siaga', 'dalam_perjalanan', 'tidak_tersedia'])],
        ]);

        $data['nicu_bed_count'] = $data['has_nicu'] ? ($data['nicu_bed_count'] ?? 0) : 0;

        $facility = Facility::create($data);

        return back()->with('success', "Fasilitas {$facility->name} berhasil ditambahkan ke direktori rujukan.");
    }

    public function update(Request $request, Facility $facility): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::in(['puskesmas', 'pustu', 'polindes', 'rumah_sakit', 'klinik'])],
            'region_code' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'hospital_class' => ['nullable', 'string', 'max:10'],
            'has_icu' => ['boolean'],
            'has_nicu' => ['boolean'],
            'nicu_bed_count' => ['nullable', 'integer', 'min:0'],
            'ambulance_status' => ['required', Rule::in(['siaga', 'dalam_perjalanan', 'tidak_tersedia'])],
        ]);

        $data['nicu_bed_count'] = $data['has_nicu'] ? ($data['nicu_bed_count'] ?? 0) : 0;

        $facility->update($data);

        return back()->with('success', "Data fasilitas {$facility->name} berhasil diperbarui.");
    }

    public function destroy(Facility $facility): RedirectResponse
    {
        $name = $facility->name;
        $facility->delete();

        return back()->with('success', "Fasilitas {$name} berhasil dihapus dari direktori.");
    }
}
