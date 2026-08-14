<?php

namespace App\Services;

use App\Models\Pregnancy;
use App\Models\ScreeningQuestion;
use Illuminate\Support\Collection;

/**
 * Urutan pertanyaan per kategori (Rules.md §3.1a). Bukan percabangan
 * kompleks — tim belum menetapkan pohon keputusan detail per pasangan
 * jawaban (lihat catatan proposal di Rules.md pembuka), jadi navigator
 * ini menyusuri daftar applicable secara berurutan dan berhenti begitu
 * RiskAssessmentEngine mendeteksi early-exit (Rules.md §3.2).
 */
class ScreeningNavigator
{
    protected const ORDER = [
        'bleeding_heavy', 'bleeding_spotting', 'bleeding_with_clots',
        'headache_severe', 'vision_blurred', 'swelling_face_hands', 'epigastric_pain', 'sudden_weight_gain',
        'fever_high', 'foul_discharge', 'painful_urination',
        'fetal_movement_reduced', 'fetal_movement_stopped',
        'abdominal_pain_severe', 'contractions_early',
        'seizure',
        'postpartum_bleeding_heavy', 'postpartum_fever', 'wound_not_healing',
        'foul_lochia', 'severe_headache_postpartum', 'breast_pain_swelling', 'mood_very_low',
    ];

    /** Pertanyaan gerakan janin hanya relevan usia kehamilan >=20 minggu (Rules.md §3.1a). */
    protected const MIN_WEEKS_FOR_FETAL_MOVEMENT = 20;

    /**
     * @return Collection<int,ScreeningQuestion> Terurut sesuai ORDER.
     */
    public function applicableQuestions(Pregnancy $pregnancy, string $sessionType): Collection
    {
        $questions = ScreeningQuestion::query()->forSessionType($sessionType)->get()->keyBy('code');

        $fetalMovementCodes = ['fetal_movement_reduced', 'fetal_movement_stopped'];
        if ($pregnancy->gestational_age_weeks_at_registration < self::MIN_WEEKS_FOR_FETAL_MOVEMENT) {
            $questions = $questions->except($fetalMovementCodes);
        }

        return collect(self::ORDER)
            ->filter(fn ($code) => $questions->has($code))
            ->map(fn ($code) => $questions->get($code))
            ->values();
    }

    public function firstQuestion(Pregnancy $pregnancy, string $sessionType): ?ScreeningQuestion
    {
        return $this->applicableQuestions($pregnancy, $sessionType)->first();
    }

    /**
     * @param  Collection<int,string>  $answeredCodes  Kode pertanyaan yang sudah punya jawaban aktif (is_superseded=false).
     */
    public function nextQuestion(Pregnancy $pregnancy, string $sessionType, Collection $answeredCodes): ?ScreeningQuestion
    {
        return $this->applicableQuestions($pregnancy, $sessionType)
            ->first(fn (ScreeningQuestion $q) => ! $answeredCodes->contains($q->code));
    }
}
