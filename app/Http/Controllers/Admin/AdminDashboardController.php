<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlertRecipient;
use App\Models\EmergencyAlert;
use App\Models\Facility;
use App\Models\HealthcareWorker;
use App\Models\KaderAreaAssignment;
use App\Models\Pregnancy;
use App\Models\RiskAssessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk Dashboard Utama & Ringkasan Statistik Administrator (Point 1).
 * Mengambil 100% data riil dan agregasi langsung dari tabel-tabel database SIGADIS.
 */
class AdminDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        // 1. Kartu Metrik Cepat (Live Database Queries)
        $totalPregnant = Pregnancy::query()->where('status', '!=', 'case_closed')->count();
        $pregnantHamil = Pregnancy::query()->where('status', 'hamil')->count();
        $pregnantNifas = Pregnancy::query()->where('status', 'nifas')->count();

        $verifiedBidan = HealthcareWorker::query()->where('status', 'verified')->where('role', 'bidan')->count();
        $verifiedKader = HealthcareWorker::query()->where('status', 'verified')->where('role', 'kader')->count();
        $pendingWorkers = HealthcareWorker::query()->where('status', 'pending')->count();
        $activeEmergencies = EmergencyAlert::query()->whereIn('status', ['pending', 'delivered', 'being_handled'])->count();

        // 2. Distribusi Risiko Kehamilan Wilayah (Live DB Agregat)
        $riskCounts = [
            'rendah' => RiskAssessment::query()->where('risk_level', 'rendah')->count(),
            'sedang' => RiskAssessment::query()->where('risk_level', 'sedang')->count(),
            'tinggi' => RiskAssessment::query()->where('risk_level', 'tinggi')->count(),
        ];
        $totalAssessments = array_sum($riskCounts);

        // 3. Indikator Rata-rata Waktu Respons Darurat (Live dari Alert Recipients DB)
        $recipientsWithAck = AlertRecipient::query()
            ->whereNotNull('acknowledged_at')
            ->with(['emergencyAlert:id,triggered_at'])
            ->get()
            ->filter(fn (AlertRecipient $r) => $r->emergencyAlert !== null && $r->emergencyAlert->triggered_at !== null);

        $responseSecondsList = [];
        foreach ($recipientsWithAck as $recipient) {
            $diff = $recipient->emergencyAlert->triggered_at->diffInSeconds($recipient->acknowledged_at, false);
            if ($diff >= 0) {
                $responseSecondsList[] = $diff;
            }
        }

        $hasResponseData = count($responseSecondsList) > 0;
        $avgResponseSeconds = $hasResponseData ? (int) round(array_sum($responseSecondsList) / count($responseSecondsList)) : 0;

        $formattedTime = $hasResponseData
            ? (floor($avgResponseSeconds / 60) > 0 ? floor($avgResponseSeconds / 60) . ' m ' : '') . ($avgResponseSeconds % 60) . ' d'
            : 'Belum ada data';

        // 4. Alert Darurat Aktif Mendadak (Urgent Alerts dari DB)
        $urgentAlerts = EmergencyAlert::query()
            ->whereIn('status', ['pending', 'delivered', 'being_handled'])
            ->with(['pregnancy.pregnantUser', 'riskAssessment'])
            ->latest('triggered_at')
            ->take(6)
            ->get()
            ->map(fn (EmergencyAlert $alert) => [
                'id' => $alert->id,
                'mother_name' => $alert->pregnancy?->mother_name ?? 'Ibu Hamil',
                'phone_number' => $alert->pregnancy?->pregnantUser?->phone_number ?? '-',
                'trigger_type' => $alert->trigger_type === 'manual_button' ? 'Tombol SOS Darurat' : 'Skrining Risiko Tinggi',
                'status' => $alert->status,
                'triggered_at' => $alert->triggered_at?->diffForHumans() ?? 'Baru saja',
                'region_code' => $alert->pregnancy?->region_code ?? '-',
            ]);

        // 5. Antrean Verifikasi Nakes Terbaru (Live DB)
        $pendingWorkersList = HealthcareWorker::query()
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (HealthcareWorker $w) => [
                'id' => $w->id,
                'full_name' => $w->full_name,
                'role' => $w->role,
                'str_or_sk' => $w->role === 'bidan' ? ($w->str_number ?? 'STR Belum Diisi') : ($w->appointment_letter_ref ?? 'SK Belum Diisi'),
                'region_code' => $w->region_code ?? '-',
                'phone_number' => $w->phone_number,
                'created_at' => $w->created_at?->diffForHumans() ?? 'Baru saja',
            ]);

        // 6. Matriks Cakupan Wilayah (Desa) - Menghimpun seluruh kode wilayah unik yang terdaftar di DB
        $distinctRegionCodes = DB::table('pregnancies')->select('region_code')
            ->union(DB::table('healthcare_workers')->select('region_code'))
            ->union(DB::table('kader_area_assignments')->select('region_code'))
            ->union(DB::table('facilities')->select('region_code'))
            ->whereNotNull('region_code')
            ->pluck('region_code')
            ->filter(fn ($code) => ! empty($code))
            ->unique()
            ->values();

        // Nama wilayah yang terdaftar di sistem faskes/wilayah
        $facilityNames = Facility::pluck('name', 'region_code')->toArray();

        $regionCoverage = $distinctRegionCodes->map(function ($code) use ($facilityNames) {
            $pregCount = Pregnancy::where('region_code', $code)->where('status', '!=', 'case_closed')->count();
            $highRisk = Pregnancy::where('region_code', $code)->whereHas('riskAssessments', fn ($q) => $q->where('risk_level', 'tinggi'))->count();
            $bidanCount = HealthcareWorker::where('region_code', $code)->where('role', 'bidan')->where('status', 'verified')->count();
            $kaderCount = KaderAreaAssignment::where('region_code', $code)->count();

            return [
                'region_code' => (string) $code,
                'village_name' => $facilityNames[$code] ?? "Wilayah {$code}",
                'total_pregnant' => $pregCount,
                'high_risk' => $highRisk,
                'bidan_count' => $bidanCount,
                'kader_count' => $kaderCount,
                'has_gap' => $kaderCount === 0 || $bidanCount === 0,
            ];
        })->toArray();

        return Inertia::render('Admin/Dashboard', [
            'metrics' => [
                'total_pregnant' => $totalPregnant,
                'pregnant_hamil' => $pregnantHamil,
                'pregnant_nifas' => $pregnantNifas,
                'verified_bidan' => $verifiedBidan,
                'verified_kader' => $verifiedKader,
                'total_workers' => $verifiedBidan + $verifiedKader,
                'pending_workers' => $pendingWorkers,
                'active_emergencies' => $activeEmergencies,
            ],
            'risk_distribution' => [
                'rendah' => $riskCounts['rendah'],
                'sedang' => $riskCounts['sedang'],
                'tinggi' => $riskCounts['tinggi'],
                'total' => $totalAssessments,
            ],
            'response_time' => [
                'avg_seconds' => $avgResponseSeconds,
                'formatted' => $formattedTime,
                'has_data' => $hasResponseData,
                'target_seconds' => 300,
                'is_on_target' => ! $hasResponseData || $avgResponseSeconds <= 300,
            ],
            'urgent_alerts' => $urgentAlerts,
            'pending_workers_list' => $pendingWorkersList,
            'region_coverage' => $regionCoverage,
        ]);
    }
}
