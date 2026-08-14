<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmergencyAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Flows.md §27.1: metrik waktu respons darurat -- selisih triggered_at ke
 * acknowledged_at pertama (bukan handled_at, itu mengukur penyelesaian
 * bukan kecepatan dilihat). §27.2/§27.3 sengaja tidak dibangun di sini,
 * itu proses manual di luar sistem (studi kasus & proyeksi eksternal).
 */
class ReportingController extends Controller
{
    public function index(Request $request): Response
    {
        [$from, $to] = $this->resolveRange($request);
        $stats = $this->responseTimeStats($from, $to);

        return Inertia::render('Admin/Reporting', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'summary' => $stats['summary'],
            'distribution' => $stats['distribution'],
        ]);
    }

    public function export(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);

        $rows = EmergencyAlert::query()
            ->whereBetween('triggered_at', [$from, $to])
            ->with(['pregnancy:id,region_code', 'riskAssessment:id,risk_level', 'recipients' => fn ($q) => $q->whereNotNull('acknowledged_at')->orderBy('acknowledged_at')])
            ->get();

        $filename = "laporan-waktu-respons-{$from->toDateString()}-sd-{$to->toDateString()}.csv";

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['alert_id', 'wilayah', 'risiko', 'trigger_type', 'triggered_at', 'acknowledged_at', 'waktu_respons_detik']);

            foreach ($rows as $alert) {
                $firstAck = $alert->recipients->first()?->acknowledged_at;
                fputcsv($handle, [
                    $alert->id,
                    $alert->pregnancy?->region_code,
                    $alert->riskAssessment?->risk_level,
                    $alert->trigger_type,
                    $alert->triggered_at->toDateTimeString(),
                    $firstAck?->toDateTimeString(),
                    $firstAck ? $alert->triggered_at->diffInSeconds($firstAck) : null,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
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
        $middle = intdiv($count, 2);

        return $count % 2 === 0
            ? ($sorted[$middle - 1] + $sorted[$middle]) / 2
            : $sorted[$middle];
    }
}
