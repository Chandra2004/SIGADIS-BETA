<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Bidan\Concerns\AuthorizesPatientManagement;
use App\Http\Controllers\Bidan\Concerns\ScopesPatientsForWorker;
use App\Http\Controllers\Controller;
use App\Models\Pregnancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Halaman detail pasien (Flows.md §13, §15): titik masuk bidan menandai
 * persalinan (transisi ke nifas) dan menutup kasus. Aksi ini khusus bidan
 * pendamping aktifnya, kader hanya bisa melihat (Flows.md §10.1).
 */
class PatientController extends Controller
{
    use AuthorizesPatientManagement, ScopesPatientsForWorker;

    public function show(Pregnancy $pregnancy): Response
    {
        $worker = Auth::guard('staff')->user();
        abort_unless($this->patientsFor($worker)->whereKey($pregnancy->id)->exists(), 403);

        $canManage = $worker->role === 'bidan';

        return Inertia::render('Desktop/PatientDetail', [
            'pregnancy' => [
                'id' => $pregnancy->id,
                'mother_name' => $pregnancy->mother_name,
                'status' => $pregnancy->status,
                'gestational_age_weeks' => $pregnancy->gestational_age_weeks_at_registration,
                'nifas_started_at' => $pregnancy->nifas_started_at,
                'delivery_notes' => $pregnancy->delivery_notes,
                'case_closed_at' => $pregnancy->case_closed_at,
                'address' => $pregnancy->address,
                'emergency_contact_name' => $pregnancy->emergency_contact_name,
                'emergency_contact_phone' => $pregnancy->emergency_contact_phone,
            ],
            'screeningSessions' => $pregnancy->screeningSessions()->with('riskAssessment')->latest('started_at')->get(),
            'referrals' => $pregnancy->referrals()->with('facility')->latest('referred_at')->get(),
            'clinicalVisits' => $pregnancy->clinicalVisits()->with('midwife:id,full_name')->latest('visited_at')->get(),
            'postpartumAssessment' => $pregnancy->postpartumAssessment,
            'caseStatus' => [
                'total_visits' => $pregnancy->screeningSessions()->count() + $pregnancy->clinicalVisits()->count(),
                'primary_midwife' => $pregnancy->activeMidwifeAssignment?->midwife?->full_name,
                'nifas_day' => $pregnancy->status === 'nifas' && $pregnancy->nifas_started_at
                    ? min(42, (int) now()->startOfDay()->diffInDays($pregnancy->nifas_started_at->startOfDay()) + 1)
                    : null,
            ],
            'canManage' => $canManage,
            'canCancelNifas' => $canManage && $this->withinNifasCorrectionWindow($pregnancy),
        ]);
    }

    /**
     * Flows.md §13.5: koreksi tanggal persalinan (bukan batal total), sama
     * jendela waktu dengan cancelNifas. Jendela dihitung dari nifas_marked_at
     * (kapan tombol ditekan), bukan nifas_started_at (tanggal medis yang bisa
     * di-backdate) — kalau dianchor ke tanggal medis, backdate lebih dari
     * sehari membuat jendela koreksi sudah lewat sejak awal dibuat.
     */
    public function editDeliveryDate(Request $request, Pregnancy $pregnancy): RedirectResponse
    {
        $this->authorizeManage($pregnancy);
        abort_unless($this->withinNifasCorrectionWindow($pregnancy), 422, 'Tanggal persalinan hanya dapat diubah dalam 24 jam pertama.');

        $data = $request->validate([
            'delivered_at' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $pregnancy->update(['nifas_started_at' => Carbon::parse($data['delivered_at'])->startOfDay()]);

        return back()->with('success', 'Tanggal persalinan diperbarui.');
    }

    /**
     * Flows.md §13.3: bidan menandai persalinan telah terjadi -> transisi nifas.
     */
    public function markDelivered(Request $request, Pregnancy $pregnancy): RedirectResponse
    {
        $this->authorizeManage($pregnancy);
        abort_unless($pregnancy->status === 'hamil', 422, 'Kehamilan ini sudah bukan status hamil.');

        $data = $request->validate([
            'delivered_at' => ['nullable', 'date', 'before_or_equal:today'],
            'delivery_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $pregnancy->update([
            'status' => 'nifas',
            'nifas_started_at' => Carbon::parse($data['delivered_at'] ?? now())->startOfDay(),
            'nifas_marked_at' => now(),
            'delivery_notes' => $data['delivery_notes'] ?? null,
        ]);

        return back()->with('success', 'Pasien ditandai telah bersalin, status beralih ke masa nifas.');
    }

    /**
     * Flows.md §13.5: koreksi salah pencet, hanya dalam 24 jam pertama.
     */
    public function cancelNifas(Pregnancy $pregnancy): RedirectResponse
    {
        $this->authorizeManage($pregnancy);
        abort_unless($this->withinNifasCorrectionWindow($pregnancy), 422, 'Status nifas hanya dapat dibatalkan dalam 24 jam pertama.');

        $pregnancy->update(['status' => 'hamil', 'nifas_started_at' => null, 'nifas_marked_at' => null]);

        return back()->with('success', 'Status nifas dibatalkan, kehamilan kembali aktif.');
    }

    protected function withinNifasCorrectionWindow(Pregnancy $pregnancy): bool
    {
        return $pregnancy->status === 'nifas' && $pregnancy->nifas_marked_at?->gt(now()->subDay());
    }

    /**
     * Flows.md §15.4/§15.6: dapat dilakukan kapan saja, tidak dikunci sampai
     * hari ke-42. Digabung dengan "Final Midwife Assessment" (desain Figma,
     * Case Closed Final Confirmation Modal) — satu aksi, bukan dua langkah
     * terpisah, checkbox konfirmasi wajib dicentang.
     */
    public function closeCase(Request $request, Pregnancy $pregnancy): RedirectResponse
    {
        $worker = $this->authorizeManage($pregnancy);
        abort_unless(in_array($pregnancy->status, ['hamil', 'nifas'], true), 422, 'Kasus ini sudah ditutup.');

        $data = $request->validate([
            'confirmed' => ['required', 'accepted'],
            'physical_recovery_status' => ['required', 'in:complete,needs_followup'],
            'infant_growth_status' => ['required', 'in:on_target,needs_monitoring'],
            'infant_weight_kg' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'family_planning_status' => ['required', 'in:counseled_decided,counseled_undecided,not_counseled'],
            'family_planning_method' => ['nullable', 'string', 'max:255'],
            'next_steps' => ['nullable', 'string', 'max:2000'],
            'final_summary_note' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($pregnancy, $worker, $data) {
            $pregnancy->postpartumAssessment()->create([
                'midwife_id' => $worker->id,
                'physical_recovery_status' => $data['physical_recovery_status'],
                'infant_growth_status' => $data['infant_growth_status'],
                'infant_weight_kg' => $data['infant_weight_kg'] ?? null,
                'family_planning_status' => $data['family_planning_status'],
                'family_planning_method' => $data['family_planning_method'] ?? null,
                'next_steps' => $data['next_steps'] ?? null,
                'final_summary_note' => $data['final_summary_note'] ?? null,
                'confirmed_at' => now(),
            ]);

            $pregnancy->update([
                'status' => 'case_closed',
                'case_closed_at' => now(),
                'case_closed_by' => $worker->id,
            ]);
        });

        return redirect()->route('bidan.dashboard')->with('success', 'Kasus pasien ditutup.');
    }
}
