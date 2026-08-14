<?php

namespace Database\Seeders;

use App\Models\ScreeningQuestion;
use Illuminate\Database\Seeder;

/**
 * Sumber: Rules.md §3.1a — [USULAN, perlu review medis sebelum dipakai
 * produksi, lihat Rules.md §7 Governance]. I=initial, P=periodic, N=nifas.
 */
class ScreeningQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            // Perdarahan
            ['bleeding_heavy', 'Apakah Ibu mengalami perdarahan banyak dari jalan lahir (lebih dari haid biasa)?', 'perdarahan', ['initial', 'periodic'], true],
            ['bleeding_spotting', 'Apakah Ibu mengalami bercak darah sedikit dari jalan lahir?', 'perdarahan', ['initial', 'periodic'], false],
            ['bleeding_with_clots', 'Apakah darah yang keluar disertai gumpalan?', 'perdarahan', ['initial', 'periodic'], false],

            // Tekanan Darah / Preeklamsia
            ['headache_severe', 'Apakah Ibu mengalami sakit kepala hebat yang tidak hilang meski sudah istirahat?', 'preeklamsia', ['initial', 'periodic'], false],
            ['vision_blurred', 'Apakah pandangan Ibu kabur, berkunang-kunang, atau ada bintik-bintik cahaya?', 'preeklamsia', ['initial', 'periodic'], false],
            ['swelling_face_hands', 'Apakah wajah atau tangan Ibu bengkak secara tiba-tiba?', 'preeklamsia', ['initial', 'periodic'], false],
            ['epigastric_pain', 'Apakah Ibu merasa nyeri hebat di ulu hati/bawah tulang rusuk kanan?', 'preeklamsia', ['initial', 'periodic'], false],
            ['sudden_weight_gain', 'Apakah berat badan Ibu naik drastis dalam waktu singkat (dalam 1 minggu)?', 'preeklamsia', ['periodic'], false],

            // Infeksi
            ['fever_high', 'Apakah Ibu demam tinggi (badan terasa sangat panas)?', 'infeksi', ['initial', 'periodic'], false],
            ['foul_discharge', 'Apakah keluar cairan berbau tidak sedap dari jalan lahir?', 'infeksi', ['initial', 'periodic'], false],
            ['painful_urination', 'Apakah Ibu merasa nyeri/panas saat buang air kecil?', 'infeksi', ['initial', 'periodic'], false],

            // Gerakan Janin
            ['fetal_movement_reduced', 'Apakah gerakan bayi terasa jauh berkurang dibanding biasanya?', 'gerakan_janin', ['periodic'], false],
            ['fetal_movement_stopped', 'Apakah bayi sama sekali tidak bergerak sejak semalam?', 'gerakan_janin', ['periodic'], true],

            // Nyeri Perut
            ['abdominal_pain_severe', 'Apakah Ibu mengalami nyeri perut hebat yang tidak biasa (bukan mulas ringan)?', 'nyeri_perut', ['initial', 'periodic'], false],
            ['contractions_early', 'Apakah Ibu merasakan kontraksi/mulas teratur padahal belum waktunya lahir?', 'nyeri_perut', ['periodic'], false],

            // Kejang
            ['seizure', 'Apakah Ibu pernah kejang (kaku/kelojotan tak terkendali) sejak kehamilan ini?', 'kejang', ['initial', 'periodic'], true],

            // Nifas
            ['postpartum_bleeding_heavy', 'Apakah darah nifas Ibu keluar sangat banyak (ganti pembalut penuh tiap kurang dari 1 jam)?', 'nifas_lain', ['nifas'], true],
            ['postpartum_fever', 'Apakah Ibu demam tinggi setelah melahirkan?', 'nifas_lain', ['nifas'], false],
            ['wound_not_healing', 'Apakah luka jahitan (bila ada) tidak kunjung membaik, bengkak, atau bernanah?', 'nifas_lain', ['nifas'], false],
            ['foul_lochia', 'Apakah keluar cairan/darah nifas berbau tidak sedap?', 'nifas_lain', ['nifas'], false],
            ['severe_headache_postpartum', 'Apakah Ibu sakit kepala hebat atau pandangan kabur setelah melahirkan?', 'nifas_lain', ['nifas'], false],
            ['breast_pain_swelling', 'Apakah payudara Ibu bengkak, merah, dan sangat nyeri disertai demam?', 'nifas_lain', ['nifas'], false],
            ['mood_very_low', 'Apakah Ibu merasa sangat sedih, putus asa, atau sulit mengurus diri/bayi selama lebih dari 2 minggu?', 'nifas_lain', ['nifas'], false],
        ];

        foreach ($questions as [$code, $text, $category, $sessions, $critical]) {
            ScreeningQuestion::updateOrCreate(
                ['code' => $code],
                [
                    'question_text' => $text,
                    'category' => $category,
                    'applies_to_session_type' => $sessions,
                    'is_critical_symptom' => $critical,
                ]
            );
        }
    }
}
