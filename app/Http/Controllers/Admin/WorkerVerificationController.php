<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HealthcareWorker;
use App\Models\KaderAreaAssignment;
use App\Models\Pregnancy;
use App\Notifications\WorkerRejectedNotification;
use App\Notifications\WorkerVerifiedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Flows.md §16.4: bidan/kader mendaftar mandiri (status pending), admin
 * puskesmas/dinkes meninjau lalu verifikasi/tolak. Flows.md §26: dashboard
 * admin lengkap — filter, badge pending, catatan, cakupan wilayah.
 */
class WorkerVerificationController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'status' => $request->query('status', 'semua'),
            'role' => $request->query('role', 'semua'),
            'region' => trim((string) $request->query('region', '')),
            'search' => trim((string) $request->query('search', '')),
        ];

        $workers = HealthcareWorker::query()
            ->when($filters['status'] !== 'semua', fn ($q) => $q->where('status', $filters['status']))
            ->when($filters['role'] !== 'semua', fn ($q) => $q->where('role', $filters['role']))
            ->when($filters['region'] !== '', fn ($q) => $q->where('region_code', $filters['region']))
            ->when($filters['search'] !== '', fn ($q) => $q->where(fn ($q2) => $q2
                ->where('full_name', 'like', "%{$filters['search']}%")
                ->orWhere('phone_number', 'like', "%{$filters['search']}%")))
            ->latest()
            ->get()
            ->map(fn (HealthcareWorker $w) => [
                ...$w->only('id', 'full_name', 'role', 'region_code', 'status', 'phone_number', 'str_number', 'appointment_letter_ref', 'admin_note', 'created_at'),
                // Flows.md §16.4.4/§13.5: jendela koreksi salah tolak.
                'can_cancel_reject' => $w->status === 'rejected' && $w->verified_at?->gt(now()->subDay()),
            ]);

        return Inertia::render('Admin/Verifikasi', [
            'filters' => $filters,
            'pendingCount' => HealthcareWorker::query()->where('status', 'pending')->count(),
            'workers' => $workers,
            'verifiedKader' => HealthcareWorker::kader()->verified()->orderBy('full_name')->get(['id', 'full_name', 'region_code']),
            'areaAssignments' => KaderAreaAssignment::with('kader:id,full_name')->orderBy('region_code')->get(),
            'coverage' => $this->regionCoverage(),
        ]);
    }

    public function verify(Request $request, HealthcareWorker $worker): RedirectResponse
    {
        abort_unless($worker->status === 'pending', 422, 'Akun ini sudah ditinjau sebelumnya.');

        $data = $request->validate(['note' => ['nullable', 'string', 'max:1000']]);

        $worker->update([
            'status' => 'verified',
            'verified_by_admin_id' => Auth::guard('admin')->id(),
            'verified_at' => now(),
            'admin_note' => $data['note'] ?? null,
        ]);
        $worker->notify(new WorkerVerifiedNotification);

        return back()->with('success', "{$worker->full_name} diverifikasi.");
    }

    public function reject(Request $request, HealthcareWorker $worker): RedirectResponse
    {
        abort_unless($worker->status === 'pending', 422, 'Akun ini sudah ditinjau sebelumnya.');

        // Flows.md §26.3.2: catatan wajib diisi saat menolak, agar ada alasan tercatat.
        $data = $request->validate(['note' => ['required', 'string', 'min:10', 'max:1000']]);

        $worker->update([
            'status' => 'rejected',
            'verified_by_admin_id' => Auth::guard('admin')->id(),
            'verified_at' => now(),
            'admin_note' => $data['note'],
        ]);
        $worker->notify(new WorkerRejectedNotification);

        return back()->with('success', "{$worker->full_name} ditolak.");
    }

    /**
     * Flows.md §16.4.4: admin salah tekan "Tolak" -> kembalikan ke pending,
     * hanya dalam 24 jam pertama (sama prinsip jendela koreksi §13.5).
     */
    public function cancelRejection(HealthcareWorker $worker): RedirectResponse
    {
        abort_unless(
            $worker->status === 'rejected' && $worker->verified_at?->gt(now()->subDay()),
            422,
            'Penolakan ini hanya dapat dibatalkan dalam 24 jam pertama.',
        );

        $worker->update(['status' => 'pending', 'verified_by_admin_id' => null, 'verified_at' => null, 'admin_note' => null]);

        return back()->with('success', "Penolakan {$worker->full_name} dibatalkan, dikembalikan ke antrean pending.");
    }

    /**
     * Flows.md §26.4: ringkasan per wilayah -- membantu identifikasi wilayah
     * tanpa kader (§24) secara proaktif. kader_primary/secondary dihitung dari
     * kader_area_assignments (dipakai eskalasi §7.1.2), bukan region_code akun.
     */
    protected function regionCoverage(): array
    {
        $regions = HealthcareWorker::query()->pluck('region_code')
            ->merge(KaderAreaAssignment::query()->pluck('region_code'))
            ->merge(Pregnancy::active()->pluck('region_code'))
            ->unique()
            ->sort()
            ->values();

        return $regions->map(fn (string $region) => [
            'region_code' => $region,
            'bidan_verified' => HealthcareWorker::bidan()->verified()->inRegion($region)->count(),
            'kader_primary' => KaderAreaAssignment::where('region_code', $region)->where('kader_priority', 'primary')
                ->whereHas('kader', fn ($q) => $q->verified())->count(),
            'kader_secondary' => KaderAreaAssignment::where('region_code', $region)->where('kader_priority', 'secondary')
                ->whereHas('kader', fn ($q) => $q->verified())->count(),
            'pregnant_count' => Pregnancy::active()->where('region_code', $region)->count(),
        ])->all();
    }
}
