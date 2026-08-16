<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Bidan\Concerns\ScopesPatientsForWorker;
use App\Http\Controllers\Controller;
use App\Models\AlertHandlingCancellation;
use App\Models\EmergencyAlert;
use App\Models\ScreeningQuestion;
use App\Notifications\AlertBeingHandledNotification;
use App\Notifications\AlertResolvedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AlertController extends Controller
{
    use ScopesPatientsForWorker;

    /** Flows.md §33.3: jendela waktu pembatalan "Terima/Tangani" salah pencet. */
    protected const CANCEL_WINDOW_MINUTES = 2;

    public function index(Request $request): Response
    {
        $worker = Auth::guard('staff')->user();
        $statusFilter = $request->query('status', 'semua');

        $query = EmergencyAlert::query()
            ->whereIn('pregnancy_id', $this->patientsFor($worker)->pluck('id'))
            ->with(['pregnancy.pregnantUser', 'riskAssessment', 'handledBy'])
            ->latest('triggered_at');

        if ($statusFilter !== 'semua') {
            $query->where('status', $statusFilter);
        }

        $alerts = $query->get()->map(fn (EmergencyAlert $a) => [
            'id' => $a->id,
            'status' => $a->status,
            'trigger_type' => $a->trigger_type,
            'triggered_at' => $a->triggered_at,
            'mother_name' => $a->pregnancy->mother_name,
            'gestational_age_weeks' => $a->pregnancy->gestational_age_weeks_at_registration,
            'address' => $a->pregnancy->address,
            'phone_number' => $a->pregnancy->pregnantUser?->phone_number,
            'emergency_contact_name' => $a->pregnancy->emergency_contact_name,
            'emergency_contact_phone' => $a->pregnancy->emergency_contact_phone,
            'risk_level' => $a->riskAssessment?->risk_level,
            'handled_by' => $a->handledBy?->full_name,
            'triggered_symptoms' => $this->triggeredSymptomLabels($a),
        ]);

        $activeCount = EmergencyAlert::query()
            ->whereIn('pregnancy_id', $this->patientsFor($worker)->pluck('id'))
            ->whereIn('status', ['pending', 'delivered', 'being_handled'])
            ->count();

        return Inertia::render('Desktop/AlertList', [
            'alerts' => $alerts,
            'statusFilter' => $statusFilter,
            'activeCount' => $activeCount,
        ]);
    }

    public function show(EmergencyAlert $alert): Response
    {
        $worker = $this->authorizeAlert($alert);
        $alert->load(['pregnancy.pregnantUser', 'riskAssessment', 'handledBy']);

        return Inertia::render('Desktop/AlertDetail', [
            'alert' => [
                'id' => $alert->id,
                'status' => $alert->status,
                'trigger_type' => $alert->trigger_type,
                'triggered_at' => $alert->triggered_at,
                'latitude' => $alert->latitude,
                'longitude' => $alert->longitude,
                'handled_by' => $alert->handledBy?->full_name,
                'escalated_to_kader_at' => $alert->escalated_to_kader_at,
                'cancel_handling_expires_at' => $alert->handled_at?->addMinutes(self::CANCEL_WINDOW_MINUTES),
                'pregnancy' => [
                    'id' => $alert->pregnancy->id,
                    'mother_name' => $alert->pregnancy->mother_name,
                    'phone_number' => $alert->pregnancy->pregnantUser?->phone_number,
                    'gestational_age_weeks' => $alert->pregnancy->gestational_age_weeks_at_registration,
                    'address' => $alert->pregnancy->address,
                    'emergency_contact_name' => $alert->pregnancy->emergency_contact_name,
                    'emergency_contact_phone' => $alert->pregnancy->emergency_contact_phone,
                ],
                'risk_level' => $alert->riskAssessment?->risk_level,
                'triggered_symptoms' => $this->triggeredSymptomLabels($alert),
                'recommendation_text' => $alert->riskAssessment?->recommendation_text,
                'can_cancel_handling' => $this->canCancelHandling($alert, $worker),
            ],
        ]);
    }

    /**
     * Flows.md §10.3.2–10.3.3: race-condition-safe — hanya penekan pertama
     * yang berhasil update baris pending/delivered ke being_handled.
     */
    public function acknowledge(Request $request, EmergencyAlert $alert): RedirectResponse
    {
        $worker = $this->authorizeAlert($alert);

        $updated = EmergencyAlert::query()
            ->whereKey($alert->id)
            ->whereIn('status', ['pending', 'delivered'])
            ->update([
                'status' => 'being_handled',
                'handled_by_id' => $worker->id,
                'handled_at' => now(),
            ]);

        if ($updated === 0) {
            $current = $alert->fresh('handledBy');

            return back()->with('info', "Sudah ditangani oleh {$current->handledBy?->full_name}.");
        }

        // Flows.md §27.1: metrik waktu respons pakai acknowledged_at (kapan pertama
        // kali dibuka/direspon), bukan handled_at -- kolomnya sudah ada di skema
        // tapi belum pernah diisi sebelum ini.
        $alert->recipients()->where('healthcare_worker_id', $worker->id)->update(['acknowledged_at' => now()]);

        $alert = $alert->fresh('handledBy');
        $alert->pregnancy->pregnantUser->notify(new AlertBeingHandledNotification($alert));

        return back()->with('success', 'Alert diterima, status diubah ke sedang ditangani.');
    }

    public function resolve(EmergencyAlert $alert): RedirectResponse
    {
        $this->authorizeAlert($alert);
        $alert->update(['status' => 'resolved']);

        $alert->pregnancy->pregnantUser->notify(new AlertResolvedNotification($alert));

        return back()->with('success', 'Penanganan ditandai selesai.');
    }

    /**
     * Flows.md §33.4/§33.6: salah pencet "Terima/Tangani" -> kembalikan
     * alert ke antrean (status 'delivered', bukan 'pending'), catat
     * pembatalan sebagai event audit terpisah (histori tetap utuh).
     */
    public function cancelHandling(EmergencyAlert $alert): RedirectResponse
    {
        $worker = $this->authorizeAlert($alert);
        abort_unless($this->canCancelHandling($alert, $worker), 422, 'Penanganan ini tidak dapat dibatalkan lagi.');

        DB::transaction(function () use ($alert) {
            AlertHandlingCancellation::create([
                'emergency_alert_id' => $alert->id,
                'cancelled_handler_id' => $alert->handled_by_id,
                'cancelled_at' => now(),
            ]);

            $alert->update(['status' => 'delivered', 'handled_by_id' => null, 'handled_at' => null]);
        });

        return redirect()->route('bidan.dashboard')->with('success', 'Penanganan dibatalkan, alert kembali ke antrean.');
    }

    /** Pola sama dengan ScreeningController::hasil() -- triggered_rule_codes berisi ScreeningQuestion.code, kecuali 'manual_activation' dari SOS manual. */
    protected function triggeredSymptomLabels(EmergencyAlert $alert): array
    {
        $codes = $alert->riskAssessment?->triggered_rule_codes ?? [];

        if (in_array('manual_activation', $codes, true)) {
            return ['Diaktifkan manual oleh Ibu (tombol SOS)'];
        }

        return ScreeningQuestion::query()->whereIn('code', $codes)->pluck('question_text')->all();
    }

    protected function canCancelHandling(EmergencyAlert $alert, $worker): bool
    {
        return $alert->status === 'being_handled'
            && $alert->handled_by_id === $worker->id
            && $alert->handled_at?->gt(now()->subMinutes(self::CANCEL_WINDOW_MINUTES))
            && $alert->referrals()->doesntExist();
    }

    public function history(EmergencyAlert $alert): Response
    {
        $this->authorizeAlert($alert);
        $pregnancy = $alert->pregnancy;

        return Inertia::render('Desktop/PatientHistory', [
            'motherName' => $pregnancy->mother_name,
            'screeningSessions' => $pregnancy->screeningSessions()->with('riskAssessment')->latest('started_at')->get(),
            'referrals' => $pregnancy->referrals()->with('facility')->latest('referred_at')->get(),
        ]);
    }

    /**
     * Flows.md §10.1: alert cuma boleh dilihat/ditangani worker yang
     * dampingan/wilayahnya cocok dengan pregnancy si alert, sama pola
     * dengan ScopesPatientsForWorker di Dashboard/PatientController.
     */
    protected function authorizeAlert(EmergencyAlert $alert)
    {
        $worker = Auth::guard('staff')->user();
        abort_unless($this->patientsFor($worker)->whereKey($alert->pregnancy_id)->exists(), 403);

        return $worker;
    }
}
