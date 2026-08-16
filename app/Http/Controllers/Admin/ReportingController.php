<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmergencyAlert;
use App\Models\Pregnancy;
use App\Models\RiskAssessment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk Laporan, Metrik & Ekspor Data (Point 7).
 * Menyediakan analisis data tren risiko maternal, evaluasi kecepatan respons darurat (KPI vs Kemenkes <5 min),
 * evaluasi kasus nifas selesai (case closed), serta ekspor dokumen laporan resmi CSV untuk Dinas Kesehatan/Puskesmas.
 */
class ReportingController extends Controller
{
    public function index(Request $request): Response
    {
        [$from, $to] = $this->resolveRange($request);
        $stats = $this->responseTimeStats($from, $to);

        // 1. Tren Penilaian Risiko Maternal dalam rentang waktu
        $riskAssessments = RiskAssessment::query()
            ->whereBetween('assessed_at', [$from, $to])
            ->get();

        $riskTrend = [
            'total' => $riskAssessments->count(),
            'rendah' => $riskAssessments->where('risk_level', 'rendah')->count(),
            'sedang' => $riskAssessments->where('risk_level', 'sedang')->count(),
            'tinggi' => $riskAssessments->where('risk_level', 'tinggi')->count(),
        ];

        // 2. Evaluasi Pasca Persalinan (Nifas & Kasus Selesai)
        $maternalOutcome = [
            'nifas_active' => Pregnancy::where('status', 'nifas')->count(),
            'case_closed_safe' => Pregnancy::where('status', 'case_closed')->count(),
        ];

        // 3. Log Riwayat Kasus Darurat dalam Rentang Waktu
        $alertLogs = EmergencyAlert::query()
            ->whereBetween('triggered_at', [$from, $to])
            ->with(['pregnancy.pregnantUser:id,full_name', 'riskAssessment:id,risk_level', 'recipients' => fn ($q) => $q->whereNotNull('acknowledged_at')->with('healthcareWorker:id,full_name,role')->orderBy('acknowledged_at')])
            ->latest('triggered_at')
            ->limit(25)
            ->get()
            ->map(function (EmergencyAlert $a) {
                $firstAckRecipient = $a->recipients->first();
                $firstAckTime = $firstAckRecipient?->acknowledged_at;
                $diff = $firstAckTime ? $a->triggered_at->diffInSeconds($firstAckTime) : null;

                return [
                    'id' => $a->id,
                    'mother_name' => $a->pregnancy?->pregnantUser?->full_name ?? ($a->pregnancy?->mother_name ?? 'Ibu Hamil'),
                    'region_code' => $a->pregnancy?->region_code ?? '-',
                    'risk_level' => $a->riskAssessment?->risk_level ?? 'tinggi',
                    'trigger_type' => $a->trigger_type === 'manual_button' ? 'Tombol SOS' : 'Skrining Risiko Tinggi',
                    'status' => $a->status,
                    'triggered_at' => $a->triggered_at->format('d/m/Y H:i'),
                    'first_responder' => $firstAckRecipient ? "{$firstAckRecipient->healthcareWorker?->full_name} ({$firstAckRecipient->healthcareWorker?->role})" : 'Belum Ada',
                    'response_seconds' => $diff,
                    'response_formatted' => $diff !== null ? ($diff < 60 ? "{$diff} detik" : round($diff / 60, 1).' menit') : '-',
                ];
            });

        return Inertia::render('Admin/Reporting', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'summary' => $stats['summary'],
            'distribution' => $stats['distribution'],
            'riskTrend' => $riskTrend,
            'maternalOutcome' => $maternalOutcome,
            'alertLogs' => $alertLogs,
        ]);
    }

    public function export(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);

        $rows = EmergencyAlert::query()
            ->whereBetween('triggered_at', [$from, $to])
            ->with(['pregnancy.pregnantUser', 'riskAssessment:id,risk_level', 'recipients' => fn ($q) => $q->whereNotNull('acknowledged_at')->with('healthcareWorker')->orderBy('acknowledged_at')])
            ->latest('triggered_at')
            ->get();

        $filename = "laporan-kesehatan-maternal-sigadis-{$from->toDateString()}-sd-{$to->toDateString()}.csv";

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['alert_id', 'nama_pasien', 'wilayah', 'tingkat_risiko', 'pemicu_alert', 'status_penanganan', 'waktu_trigger', 'waktu_respons', 'waktu_respons_detik', 'nakes_penanggap']);

            foreach ($rows as $alert) {
                $firstAck = $alert->recipients->first();
                $ackTime = $firstAck?->acknowledged_at;
                $diffSeconds = $ackTime ? $alert->triggered_at->diffInSeconds($ackTime) : '';

                fputcsv($handle, [
                    $alert->id,
                    $alert->pregnancy?->pregnantUser?->full_name ?? ($alert->pregnancy?->mother_name ?? 'Ibu Hamil'),
                    $alert->pregnancy?->region_code ?? '-',
                    $alert->riskAssessment?->risk_level ?? 'tinggi',
                    $alert->trigger_type,
                    $alert->status,
                    $alert->triggered_at->toDateTimeString(),
                    $ackTime?->toDateTimeString() ?? 'Belum Direspon',
                    $diffSeconds,
                    $firstAck?->healthcareWorker?->full_name ?? '-',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /** @return array{0: Carbon, 1: Carbon} */
    protected function resolveRange(Request $request): array
    {
        $from = $request->query('from') ? Carbon::parse($request->query('from'))->startOfDay() : now()->subDays(30)->startOfDay();
        $to = $request->query('to') ? Carbon::parse($request->query('to'))->endOfDay() : now()->endOfDay();

        return [$from, $to];
    }

    protected function responseTimeStats(Carbon $from, Carbon $to): array
    {
        $alerts = EmergencyAlert::query()
            ->whereBetween('triggered_at', [$from, $to])
            ->with(['recipients' => fn ($q) => $q->whereNotNull('acknowledged_at')->orderBy('acknowledged_at')])
            ->get();

        $responseSeconds = $alerts
            ->map(function (EmergencyAlert $alert) {
                $firstAck = $alert->recipients->first()?->acknowledged_at;

                return $firstAck ? $alert->triggered_at->diffInSeconds($firstAck) : null;
            })
            ->filter(fn ($v) => $v !== null)
            ->sort()
            ->values();

        $count = $responseSeconds->count();

        return [
            'summary' => [
                'total_alerts' => $alerts->count(),
                'responded_count' => $count,
                'unresponded_count' => $alerts->count() - $count,
                'average_seconds' => $count ? (int) round($responseSeconds->avg()) : null,
                'median_seconds' => $count ? (int) round($this->median($responseSeconds)) : null,
            ],
            'distribution' => [
                'under_5min' => $responseSeconds->filter(fn ($s) => $s < 300)->count(),
                'between_5_10min' => $responseSeconds->filter(fn ($s) => $s >= 300 && $s < 600)->count(),
                'between_10_30min' => $responseSeconds->filter(fn ($s) => $s >= 600 && $s < 1800)->count(),
                'over_30min' => $responseSeconds->filter(fn ($s) => $s >= 1800)->count(),
            ],
        ];
    }

    protected function median(Collection $sorted): float
    {
        $count = $sorted->count();
        if ($count === 0) {
            return 0.0;
        }

        $middle = intdiv($count, 2);

        return $count % 2 === 0
            ? ($sorted[$middle - 1] + $sorted[$middle]) / 2
            : $sorted[$middle];
    }
}
