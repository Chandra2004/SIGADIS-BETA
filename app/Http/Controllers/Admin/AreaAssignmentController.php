<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\HealthcareWorker;
use App\Models\KaderAreaAssignment;
use App\Models\Pregnancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk Zonasi & Penugasan Wilayah Kader (Point 3).
 * Mengatur matriks cakupan desa, hierarki eskalasi darurat (Primary/Secondary Kader),
 * dan mendeteksi celah cakupan (Gap Alert) agar tidak ada desa tanpa kader (zero coverage).
 */
class AreaAssignmentController extends Controller
{
    public function index(Request $request): Response
    {
        // 1. Himpun seluruh wilayah unik yang aktif di database
        $distinctRegionCodes = DB::table('pregnancies')->select('region_code')
            ->union(DB::table('healthcare_workers')->select('region_code'))
            ->union(DB::table('kader_area_assignments')->select('region_code'))
            ->union(DB::table('facilities')->select('region_code'))
            ->whereNotNull('region_code')
            ->pluck('region_code')
            ->filter(fn ($c) => ! empty($c))
            ->unique()
            ->values();

        $facilityNames = Facility::pluck('name', 'region_code')->toArray();

        // 2. Hitung Matriks Cakupan Wilayah (Coverage Matrix)
        $coverage = $distinctRegionCodes->map(function ($code) use ($facilityNames) {
            $pregCount = Pregnancy::where('region_code', $code)->where('status', '!=', 'case_closed')->count();
            $highRisk = Pregnancy::where('region_code', $code)->whereHas('riskAssessments', fn ($q) => $q->where('risk_level', 'tinggi'))->count();
            $bidanCount = HealthcareWorker::where('region_code', $code)->where('role', 'bidan')->where('status', 'verified')->count();
            $kaderCount = KaderAreaAssignment::where('region_code', $code)->count();
            $primaryKaders = KaderAreaAssignment::where('region_code', $code)->where('kader_priority', 'primary')->with('kader:id,full_name')->get();
            $secondaryKaders = KaderAreaAssignment::where('region_code', $code)->where('kader_priority', 'secondary')->with('kader:id,full_name')->get();

            return [
                'region_code' => (string) $code,
                'village_name' => $facilityNames[$code] ?? "Wilayah {$code}",
                'total_pregnant' => $pregCount,
                'high_risk' => $highRisk,
                'bidan_count' => $bidanCount,
                'kader_count' => $kaderCount,
                'primary_kaders' => $primaryKaders->pluck('kader.full_name')->filter()->values(),
                'secondary_kaders' => $secondaryKaders->pluck('kader.full_name')->filter()->values(),
                'has_gap' => $kaderCount === 0 || $bidanCount === 0,
                'gap_type' => ($kaderCount === 0 && $bidanCount === 0) ? 'no_workers' : ($kaderCount === 0 ? 'no_kader' : ($bidanCount === 0 ? 'no_bidan' : 'safe')),
            ];
        })->values();

        // 3. Daftar Penugasan Kader Aktif
        $assignments = KaderAreaAssignment::with('kader:id,full_name,phone_number,status')
            ->latest()
            ->get()
            ->map(fn (KaderAreaAssignment $a) => [
                'id' => $a->id,
                'kader_id' => $a->kader_id,
                'kader_name' => $a->kader?->full_name ?? 'Kader',
                'phone_number' => $a->kader?->phone_number ?? '-',
                'region_code' => $a->region_code,
                'region_name' => $facilityNames[$a->region_code] ?? "Wilayah {$a->region_code}",
                'kader_priority' => $a->kader_priority, // 'primary' | 'secondary'
                'created_at' => $a->created_at?->diffForHumans() ?? 'Baru saja',
            ]);

        // 4. Kader Terverifikasi yang Siap Ditugaskan
        $verifiedKaders = HealthcareWorker::kader()
            ->verified()
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'phone_number', 'region_code']);

        // 5. Metrik Ringkasan Zonasi
        $totalRegions = $coverage->count();
        $safeRegions = $coverage->where('has_gap', false)->count();
        $gapRegions = $totalRegions - $safeRegions;

        return Inertia::render('Admin/Zonasi', [
            'coverage' => $coverage,
            'assignments' => $assignments,
            'verifiedKaders' => $verifiedKaders,
            'metrics' => [
                'total_regions' => $totalRegions,
                'safe_regions' => $safeRegions,
                'gap_regions' => $gapRegions,
                'total_assignments' => $assignments->count(),
            ],
        ]);
    }

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

        return back()->with('success', "Kader {$kader->full_name} berhasil ditugaskan ke wilayah {$data['region_code']} sebagai {$data['kader_priority']}.");
    }

    public function destroy(KaderAreaAssignment $areaAssignment): RedirectResponse
    {
        $kaderName = $areaAssignment->kader?->full_name ?? 'Kader';
        $region = $areaAssignment->region_code;
        $areaAssignment->delete();

        return back()->with('success', "Penugasan {$kaderName} di wilayah {$region} berhasil dihapus.");
    }
}
