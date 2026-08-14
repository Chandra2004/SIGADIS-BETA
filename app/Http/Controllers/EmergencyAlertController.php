<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCurrentPregnancy;
use App\Services\EmergencyAlertService;
use App\Services\RiskAssessmentEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmergencyAlertController extends Controller
{
    use ResolvesCurrentPregnancy;

    public function __construct(
        protected RiskAssessmentEngine $engine,
        protected EmergencyAlertService $alerts,
    ) {}

    /**
     * Tombol darurat manual (Flows.md §8.3): selalu risiko tinggi, tidak
     * lewat decision tree. Anti-duplikat kalau sudah ada alert terbuka
     * (§8.6 Kasus A). Lokasi GPS opsional — dari browser/app (izin bisa
     * ditolak, lihat toggle Privasi & Data Saya), tidak wajib buat kirim SOS.
     */
    public function activate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $pregnancy = $this->currentPregnancy() ?? abort(404);

        if ($this->alerts->hasOpenAlert($pregnancy)) {
            return redirect()->route('darurat.status')
                ->with('success', 'Peringatan darurat Ibu sudah terkirim dan sedang ditangani.');
        }

        $result = $this->engine->manualActivation();

        $riskAssessment = $pregnancy->riskAssessments()->create([
            'screening_session_id' => null,
            'risk_level' => $result['risk_level'],
            'triggered_rule_codes' => $result['triggered_rule_codes'],
            'is_data_incomplete' => false,
            'recommendation_text' => $result['recommendation_text'],
            'disclaimer_shown' => true,
            'assessed_at' => now(),
        ]);

        $this->alerts->triggerManual($pregnancy, $riskAssessment, $data['latitude'] ?? null, $data['longitude'] ?? null);

        return redirect()->route('darurat.status')
            ->with('success', 'Peringatan darurat terkirim. Bidan dan kader Ibu akan segera dihubungi.');
    }
}
