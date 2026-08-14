<?php

namespace App\Services;

use App\Models\Pregnancy;
use App\Models\ScreeningQuestion;
use Illuminate\Support\Collection;

/**
 * Rule-based decision tree, BUKAN machine learning (Rules.md §1).
 *
 * [USULAN — perlu review medis, Rules.md §7]. Ini kerangka awal yang
 * menerjemahkan Rules.md §3.2–§3.3 ke kode, bukan pohon keputusan final.
 * Ambang/bobot numerik wajib ditinjau dosen pembimbing/mitra nakes
 * sebelum dipakai di produksi.
 */
class RiskAssessmentEngine
{
    /** Kombinasi gejala preeklamsia (Rules.md §1 contoh ilustratif, §3.2). */
    protected const PREEKLAMPSIA_CODES = ['headache_severe', 'vision_blurred', 'swelling_face_hands', 'epigastric_pain'];

    /** Perdarahan/nyeri perut yang ambangnya diturunkan pada kehamilan kembar (Rules.md §3.3). */
    protected const BLEEDING_PAIN_CODES = ['bleeding_spotting', 'bleeding_with_clots', 'abdominal_pain_severe'];

    /**
     * @param  Collection<string,string>  $answers  code => 'ya'|'tidak', HANYA jawaban is_superseded=false
     */
    public function evaluate(Pregnancy $pregnancy, Collection $answers): array
    {
        $yaCodes = $answers->filter(fn ($v) => $v === 'ya')->keys();

        if ($yaCodes->isEmpty()) {
            return $this->result('rendah', [], 'Tidak ditemukan gejala peringatan signifikan. Lanjutkan monitoring rutin melalui skrining berkala.');
        }

        if ($critical = $this->criticalHit($yaCodes)) {
            return $this->result('tinggi', $critical->all(), 'Segera hubungi bidan Ibu untuk evaluasi lebih lanjut. Ini kondisi yang perlu penanganan cepat.');
        }

        if ($combo = $this->preeklampsiaComboHit($pregnancy, $yaCodes)) {
            return $this->result('tinggi', $combo->all(), 'Kombinasi gejala Ibu mengarah ke tekanan darah tinggi berbahaya saat hamil. Segera hubungi bidan Ibu.');
        }

        if ($twinBleeding = $this->twinPregnancyLoweredThresholdHit($pregnancy, $yaCodes)) {
            return $this->result('tinggi', $twinBleeding->all(), 'Pada kehamilan kembar, gejala ini perlu perhatian ekstra. Segera hubungi bidan Ibu.');
        }

        return $this->result('sedang', $yaCodes->all(), 'Ada gejala yang perlu dipantau. Sebaiknya Ibu kontrol lebih cepat ke bidan, tidak perlu menunggu jadwal berikutnya.');
    }

    public function manualActivation(): array
    {
        return $this->result('tinggi', ['manual_activation'], 'Peringatan darurat diaktifkan manual oleh Ibu.');
    }

    protected function criticalHit(Collection $yaCodes): ?Collection
    {
        $criticalCodes = ScreeningQuestion::query()->critical()->pluck('code');
        $hit = $yaCodes->intersect($criticalCodes);

        return $hit->isNotEmpty() ? $hit : null;
    }

    protected function preeklampsiaComboHit(Pregnancy $pregnancy, Collection $yaCodes): ?Collection
    {
        $hit = $yaCodes->intersect(self::PREEKLAMPSIA_CODES);

        // Diabetes gestasional/hipertensi kronis menurunkan ambang kombinasi (Rules.md §3.3).
        $threshold = ($pregnancy->has_gestational_diabetes || $pregnancy->has_chronic_hypertension) ? 1 : 2;

        return $hit->count() >= $threshold ? $hit : null;
    }

    protected function twinPregnancyLoweredThresholdHit(Pregnancy $pregnancy, Collection $yaCodes): ?Collection
    {
        if (! $pregnancy->is_twin_pregnancy) {
            return null;
        }

        $hit = $yaCodes->intersect(self::BLEEDING_PAIN_CODES);

        return $hit->isNotEmpty() ? $hit : null;
    }

    protected function result(string $level, array $triggeredCodes, string $recommendation): array
    {
        return [
            'risk_level' => $level,
            'triggered_rule_codes' => $triggeredCodes,
            'recommendation_text' => $recommendation,
        ];
    }
}
