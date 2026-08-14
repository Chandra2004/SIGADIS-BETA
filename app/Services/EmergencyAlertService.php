<?php

namespace App\Services;

use App\Events\EmergencyAlertBroadcast;
use App\Models\EmergencyAlert;
use App\Models\KaderAreaAssignment;
use App\Models\Pregnancy;
use App\Models\RiskAssessment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Fitur 4 — Emergency Alert (Flows.md §7.1, §8.3). Pengiriman ke penerima
 * dua jalur (Architecture.md §5.2/§5.4): broadcast Reverb real-time ke
 * dashboard yang sedang terbuka, + push FCM untuk app di background
 * (SendPushForEmergencyAlert, listener EmergencyAlertBroadcast). Baris
 * alert_recipients tetap "pending" — retry/delivery-status per penerima
 * belum ada, itu iterasi berikutnya.
 */
class EmergencyAlertService
{
    /**
     * Fail-safe anti-duplikat (Flows.md §8.6 Kasus A): kalau pregnancy ini
     * masih punya alert terbuka, jangan buat baris baru.
     */
    public function hasOpenAlert(Pregnancy $pregnancy): bool
    {
        return $pregnancy->emergencyAlerts()->open()->exists();
    }

    public function triggerFromRiskAssessment(Pregnancy $pregnancy, RiskAssessment $riskAssessment): EmergencyAlert
    {
        return $this->create($pregnancy, 'auto_risk_high', $riskAssessment->id);
    }

    /** Flows.md: dialog SOS ambil GPS ibu hamil, dikirim ke bidan/kader. */
    public function triggerManual(Pregnancy $pregnancy, RiskAssessment $riskAssessment, ?float $latitude = null, ?float $longitude = null): EmergencyAlert
    {
        return $this->create($pregnancy, 'manual_button', $riskAssessment->id, $latitude, $longitude);
    }

    protected function create(Pregnancy $pregnancy, string $triggerType, int $riskAssessmentId, ?float $latitude = null, ?float $longitude = null): EmergencyAlert
    {
        return DB::transaction(function () use ($pregnancy, $triggerType, $riskAssessmentId, $latitude, $longitude) {
            $alert = EmergencyAlert::create([
                'pregnancy_id' => $pregnancy->id,
                'trigger_type' => $triggerType,
                'risk_assessment_id' => $riskAssessmentId,
                'status' => 'pending',
                'triggered_at' => now(),
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);

            $recipientIds = $this->assignRecipients($alert, $pregnancy);

            if ($recipientIds !== []) {
                // afterCommit, bukan langsung: dispatch ShouldBroadcast/ShouldQueue di
                // dalam transaksi lomba dengan worker queue yang dequeue lebih cepat
                // dari commit (QUEUE_CONNECTION=database, after_commit=false default) —
                // job bisa gagal fetch baris yang belum ter-commit. create() ini juga
                // dipanggil dari dalam transaksi luar (FinalizeScreeningSessionAction),
                // afterCommit tetap aman karena baru jalan begitu transaksi terluar commit.
                DB::afterCommit(fn () => event(new EmergencyAlertBroadcast($alert->fresh(), $recipientIds)));
            }

            return $alert->fresh('recipients');
        });
    }

    /**
     * Flows.md §7.3.2: alert yang belum di-acknowledge (masih pending/delivered)
     * melewati batas waktu -> tambahkan kader secondary wilayah. Dipanggil dari
     * scheduler (lihat routes/console.php, perintah alerts:escalate).
     *
     * Flows.md §34.3.1: kalau bidan pendamping utama pasien sedang berstatus
     * nonaktif sementara, jangan tunggu batas waktu normal — eskalasi
     * dipercepat pakai escalation_timeout_when_midwife_unavailable_minutes
     * [USULAN, penyesuaian logika eskalasi — perlu didiskusikan tim, ini
     * interpretasi kami: bidan tetap tercatat sebagai penerima (alert tetap
     * terkirim ke dia), tapi jendela tunggu sebelum kader dilibatkan lebih pendek].
     */
    public function escalateOverdue(): int
    {
        $normalTimeout = config('emergency_alert.escalation_timeout_minutes');
        $acceleratedTimeout = config('emergency_alert.escalation_timeout_when_midwife_unavailable_minutes');

        $overdue = EmergencyAlert::query()
            ->whereIn('status', ['pending', 'delivered'])
            ->whereNull('escalated_to_kader_at')
            ->with('pregnancy.activeMidwifeAssignment.midwife')
            ->where('triggered_at', '<=', now()->subMinutes($acceleratedTimeout))
            ->get()
            ->filter(function (EmergencyAlert $alert) use ($normalTimeout, $acceleratedTimeout) {
                $midwifeUnavailable = $alert->pregnancy->activeMidwifeAssignment?->midwife?->is_available === false;
                $timeout = $midwifeUnavailable ? $acceleratedTimeout : $normalTimeout;

                return $alert->triggered_at->lte(now()->subMinutes($timeout));
            });

        foreach ($overdue as $alert) {
            $this->escalateToSecondaryKader($alert);
        }

        return $overdue->count();
    }

    protected function escalateToSecondaryKader(EmergencyAlert $alert): void
    {
        $pregnancy = $alert->pregnancy;
        $existingRecipientIds = $alert->recipients()->pluck('healthcare_worker_id');

        $secondaryKaderIds = KaderAreaAssignment::query()
            ->where('region_code', $pregnancy->region_code)
            ->where('kader_priority', 'secondary')
            ->whereHas('kader', fn ($q) => $q->available())
            ->pluck('kader_id')
            ->diff($existingRecipientIds)
            ->values();

        DB::transaction(function () use ($alert, $secondaryKaderIds) {
            $alert->update(['escalated_to_kader_at' => now()]);

            foreach ($secondaryKaderIds as $kaderId) {
                $alert->recipients()->create([
                    'healthcare_worker_id' => $kaderId,
                    'recipient_role_at_time' => 'kader_secondary',
                    'delivery_status' => 'pending',
                ]);
            }
        });

        if ($secondaryKaderIds->isNotEmpty()) {
            $ids = $secondaryKaderIds->all();
            DB::afterCommit(fn () => event(new EmergencyAlertBroadcast($alert->fresh(), $ids)));

            return;
        }

        // Flows.md §24.3: tidak ada kader secondary di wilayah untuk eskalasi.
        // Fallback 'admin_fallback' sengaja tidak dibangun — alert_recipients.
        // healthcare_worker_id adalah FK ke healthcare_workers, bukan
        // admin_users, dan dokumen sumber sendiri menandai ini butuh
        // penyesuaian skema terpisah [USULAN, belum diputuskan]. Jalur yang
        // dibangun di sini adalah opsi kedua §24.3: transparansi eksplisit
        // lewat log, bukan diam-diam gagal.
        Log::warning("Emergency alert #{$alert->id}: eskalasi ke kader secondary gagal, tidak ada kader cadangan di wilayah {$alert->pregnancy->region_code}.");
    }

    /**
     * Penerima awal (Flows.md §7.1.2): bidan pendamping aktif + kader
     * primary wilayah.
     *
     * @return array<int,int> ID healthcare_worker penerima, dipakai buat broadcast.
     */
    protected function assignRecipients(EmergencyAlert $alert, Pregnancy $pregnancy): array
    {
        $recipientIds = [];

        $midwife = $pregnancy->activeMidwifeAssignment?->midwife;

        if ($midwife) {
            $alert->recipients()->create([
                'healthcare_worker_id' => $midwife->id,
                'recipient_role_at_time' => 'bidan_utama',
                'delivery_status' => 'pending',
            ]);
            $recipientIds[] = $midwife->id;
        }

        // Flows.md §34.3.2: kader nonaktif sementara dikecualikan dari pencarian penerima.
        $primaryKaderIds = KaderAreaAssignment::query()
            ->where('region_code', $pregnancy->region_code)
            ->where('kader_priority', 'primary')
            ->whereHas('kader', fn ($q) => $q->available())
            ->pluck('kader_id');

        foreach ($primaryKaderIds as $kaderId) {
            $alert->recipients()->create([
                'healthcare_worker_id' => $kaderId,
                'recipient_role_at_time' => 'kader_primary',
                'delivery_status' => 'pending',
            ]);
            $recipientIds[] = $kaderId;
        }

        // Flows.md §24.2: tidak ada kader primary terdaftar di wilayah ini —
        // alert tetap terkirim penuh ke bidan (jalur utama tidak terganggu),
        // dicatat sebagai celah cakupan wilayah, bukan silent failure.
        if ($primaryKaderIds->isEmpty()) {
            Log::warning("Emergency alert #{$alert->id}: tidak ada kader primary terdaftar di wilayah {$pregnancy->region_code}.");
        }

        return $recipientIds;
    }
}
