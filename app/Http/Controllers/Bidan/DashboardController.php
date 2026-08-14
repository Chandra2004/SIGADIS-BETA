<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Bidan\Concerns\ScopesPatientsForWorker;
use App\Http\Controllers\Controller;
use App\Models\Pregnancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    use ScopesPatientsForWorker;

    /** Filter tabel pasien: ?filter=tinggi|sedang|rendah|nifas, default semua. */
    public function index(Request $request): Response
    {
        $worker = Auth::guard('staff')->user();
        $filter = $request->query('filter', 'semua');

        $patients = $this->patientsFor($worker)
            ->with([
                'riskAssessments' => fn ($q) => $q->latest('assessed_at')->limit(1),
                'screeningSessions' => fn ($q) => $q->latest('started_at')->limit(1),
            ])
            ->get()
            ->map(fn (Pregnancy $p) => [
                'id' => $p->id,
                'mother_name' => $p->mother_name,
                'status' => $p->status,
                'gestational_age_weeks' => $p->gestational_age_weeks_at_registration,
                'last_risk_level' => $p->riskAssessments->first()?->risk_level,
                'last_screening_at' => $p->screeningSessions->first()?->started_at,
                // Flows.md §15.1: 42 hari nifas terlewati tanpa alert terbuka -> tinjau penutupan kasus.
                'nifas_overdue' => $p->status === 'nifas' && $p->nifas_started_at?->lt(now()->subDays(42)),
            ]);

        return Inertia::render('Desktop/Dashboard', [
            'worker' => [
                ...$worker->only('id', 'full_name', 'role', 'region_code'),
                'is_available' => $worker->is_available,
                'unavailable_until' => $worker->unavailable_until,
            ],
            'summary' => [
                'total' => $patients->count(),
                'risiko_tinggi' => $patients->where('last_risk_level', 'tinggi')->count(),
                'risiko_sedang' => $patients->where('last_risk_level', 'sedang')->count(),
                'nifas' => $patients->where('status', 'nifas')->count(),
            ],
            'filter' => $filter,
            'patients' => $this->applyFilter($patients, $filter)->values(),
            'pendingAlerts' => $this->patientsFor($worker)
                ->with(['emergencyAlerts' => fn ($q) => $q->open()->latest('triggered_at')])
                ->get()
                ->flatMap(fn (Pregnancy $p) => $p->emergencyAlerts->map(fn ($a) => [
                    'id' => $a->id,
                    'pregnancy_id' => $p->id,
                    'mother_name' => $p->mother_name,
                    'status' => $a->status,
                    'triggered_at' => $a->triggered_at,
                ]))
                ->sortByDesc('triggered_at')
                ->values(),
        ]);
    }

    protected function applyFilter($patients, string $filter)
    {
        return match ($filter) {
            'tinggi' => $patients->where('last_risk_level', 'tinggi'),
            'sedang' => $patients->where('last_risk_level', 'sedang'),
            'rendah' => $patients->where('last_risk_level', 'rendah'),
            'nifas' => $patients->where('status', 'nifas'),
            default => $patients,
        };
    }
}
