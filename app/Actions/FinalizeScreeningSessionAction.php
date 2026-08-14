<?php

namespace App\Actions;

use App\Models\RiskAssessment;
use App\Models\ScreeningSession;
use App\Services\EmergencyAlertService;
use App\Services\RiskAssessmentEngine;
use App\Services\ScreeningNavigator;
use Illuminate\Support\Facades\DB;

/**
 * Flows.md §4.4, §5.1, §5.3: tutup sesi, jalankan Risk Engine, dan kalau
 * hasilnya tinggi, langsung trigger Emergency Alert — sebelum/bersamaan
 * layar hasil ditampilkan, tidak menunggu aksi pengguna lain.
 */
class FinalizeScreeningSessionAction
{
    public function __construct(
        protected RiskAssessmentEngine $engine,
        protected ScreeningNavigator $navigator,
        protected EmergencyAlertService $alerts,
    ) {}

    public function handle(ScreeningSession $session): RiskAssessment
    {
        return DB::transaction(function () use ($session) {
            $pregnancy = $session->pregnancy;

            $answers = $session->answers()
                ->where('is_superseded', false)
                ->with('question')
                ->get()
                ->mapWithKeys(fn ($a) => [$a->question->code => $a->answer]);

            $result = $this->engine->evaluate($pregnancy, $answers);

            $isDataIncomplete = $result['risk_level'] !== 'tinggi'
                && $this->hasUnansweredApplicableQuestions($session, $pregnancy, $answers->keys());

            $session->update(['completed_at' => now(), 'is_complete' => true]);

            $riskAssessment = $pregnancy->riskAssessments()->create([
                'screening_session_id' => $session->id,
                'risk_level' => $result['risk_level'],
                'triggered_rule_codes' => $result['triggered_rule_codes'],
                'is_data_incomplete' => $isDataIncomplete,
                'recommendation_text' => $result['recommendation_text'],
                'disclaimer_shown' => true,
                'assessed_at' => now(),
            ]);

            if ($result['risk_level'] === 'tinggi' && ! $this->alerts->hasOpenAlert($pregnancy)) {
                $this->alerts->triggerFromRiskAssessment($pregnancy, $riskAssessment);
            }

            return $riskAssessment;
        });
    }

    protected function hasUnansweredApplicableQuestions(ScreeningSession $session, $pregnancy, $answeredCodes): bool
    {
        $applicableCodes = $this->navigator->applicableQuestions($pregnancy, $session->session_type)->pluck('code');

        return $applicableCodes->diff($answeredCodes)->isNotEmpty();
    }
}
