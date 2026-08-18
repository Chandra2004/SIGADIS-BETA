<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\EmergencyAlert;
use App\Models\Pregnancy;
use App\Models\RiskAssessment;
use App\Models\ScreeningAnswer;
use App\Models\ScreeningQuestion;
use App\Models\ScreeningSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MobileScreeningController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $user = Auth::guard('pregnant')->user();

        if (! $user) {
            return redirect()->route('mobile.login.show');
        }

        $activePregnancyId = session('active_pregnancy_id');
        $pregnancy = Pregnancy::where('pregnant_user_id', $user->id)
            ->when($activePregnancyId, fn ($q) => $q->where('id', $activePregnancyId))
            ->first();

        if (! $pregnancy) {
            return redirect()->route('mobile.pregnancy.register.show');
        }

        // Tentukan tipe sesi skrining: initial, periodic, atau nifas
        $sessionType = match ($pregnancy->status) {
            'nifas' => 'nifas',
            default => $pregnancy->screeningSessions()->count() === 0 ? 'initial' : 'periodic',
        };

        // Ambil bank soal skrining yang sesuai dengan tipe sesi
        $questions = ScreeningQuestion::all()
            ->filter(function ($q) use ($sessionType) {
                $sessions = is_array($q->applicable_sessions) ? $q->applicable_sessions : [];
                return in_array($sessionType, $sessions);
            })
            ->values()
            ->map(function ($q) {
                return [
                    'id' => $q->id,
                    'code' => $q->code,
                    'question_text' => $q->question_text,
                    'category' => $q->category,
                    'is_critical' => (bool) $q->is_critical_symptom,
                ];
            });

        return Inertia::render('Mobile/Screening', [
            'motherName' => $pregnancy->mother_name,
            'sessionType' => $sessionType,
            'gestationalAgeWeeks' => $pregnancy->currentGestationalAgeWeeks(),
            'pregnancyStatus' => $pregnancy->status,
            'questions' => $questions,
            'consentRevoked' => ! $pregnancy->hasActiveConsent(),
        ]);
    }

    public function submit(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::guard('pregnant')->user();
        $pregnancyId = session('active_pregnancy_id');

        $pregnancy = Pregnancy::where('pregnant_user_id', $user->id)
            ->when($pregnancyId, fn ($q) => $q->where('id', $pregnancyId))
            ->first();

        if (! $pregnancy) {
            return response()->json(['error' => 'Kehamilan tidak ditemukan'], 404);
        }

        if (! $pregnancy->hasActiveConsent()) {
            return response()->json([
                'error' => 'Persetujuan pemrosesan data untuk profil ini sedang dicabut. Silakan aktifkan kembali di menu Pengaturan > Privasi & Data Saya.',
            ], 403);
        }

        $validated = $request->validate([
            'session_type' => ['required', 'string', 'in:initial,periodic,nifas'],
            'answers' => ['required', 'array'],
            'answers.*.question_id' => ['required'],
            'answers.*.code' => ['required', 'string'],
            'answers.*.answer' => ['nullable', 'string', 'in:ya,tidak,skip'],
            'answers.*.used_tts' => ['nullable', 'boolean'],
        ]);

        // Buat sesi skrining baru
        $session = ScreeningSession::create([
            'pregnancy_id' => $pregnancy->id,
            'session_type' => $validated['session_type'],
            'started_at' => now()->subMinutes(2),
            'completed_at' => now(),
            'is_complete' => true,
        ]);

        $hasCriticalYes = false;
        $hasModerateYes = false;
        $triggeredCodes = [];
        $hasSkipped = false;

        foreach ($validated['answers'] as $ans) {
            $question = ScreeningQuestion::where('code', $ans['code'])->first();
            $answerVal = $ans['answer'];

            if ($answerVal === 'skip' || is_null($answerVal)) {
                $hasSkipped = true;
                continue;
            }

            ScreeningAnswer::create([
                'screening_session_id' => $session->id,
                'screening_question_id' => $question?->id ?? 1,
                'answer' => $answerVal,
                'used_text_to_speech' => (bool) ($ans['used_tts'] ?? false),
                'answered_at' => now(),
            ]);

            if ($answerVal === 'ya') {
                $triggeredCodes[] = $ans['code'];
                if ($question && $question->is_critical_symptom) {
                    $hasCriticalYes = true;
                } else {
                    $hasModerateYes = true;
                }
            }
        }

        // Klasifikasi Tingkat Risiko (WHO / Kemenkes Decision Tree)
        $riskLevel = 'rendah';
        $recommendation = 'Kondisi Ibu dan kandungan tampak sehat dan aman. Tetap jaga nutrisi, istirahat cukup, dan ikuti jadwal kontrol kehamilan rutin.';

        if ($hasCriticalYes || in_array('bleeding_heavy', $triggeredCodes) || in_array('seizure', $triggeredCodes) || in_array('fetal_movement_stopped', $triggeredCodes) || in_array('postpartum_bleeding_heavy', $triggeredCodes)) {
            $riskLevel = 'tinggi';
            $recommendation = 'Terdeteksi tanda bahaya kritis kegawatdaruratan maternal! Peringatan darurat telah dikirimkan secara otomatis ke Bidan dan Kader pendamping Anda.';
        } elseif ($hasModerateYes || count($triggeredCodes) > 0 || $pregnancy->has_chronic_hypertension || $pregnancy->has_gestational_diabetes) {
            $riskLevel = 'sedang';
            $recommendation = 'Ditemukan gejala yang memerlukan pemantauan medis lebih lanjut. Disarankan untuk beristirahat dan berkonsultasi dengan Bidan pendamping dalam 1–2 hari.';
        }

        // Catat Risk Assessment
        $assessment = RiskAssessment::create([
            'pregnancy_id' => $pregnancy->id,
            'screening_session_id' => $session->id,
            'risk_level' => $riskLevel,
            'triggered_rule_codes' => $triggeredCodes,
            'recommendation_text' => $recommendation,
            'is_data_incomplete' => $hasSkipped,
        ]);

        // Jika Risiko Tinggi: Kirim Otomatis Emergency Alert ke Bidan & Kader
        if ($riskLevel === 'tinggi') {
            EmergencyAlert::create([
                'pregnancy_id' => $pregnancy->id,
                'risk_assessment_id' => $assessment->id,
                'trigger_type' => 'auto_risk_high',
                'status' => 'pending',
                'triggered_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'risk_level' => $riskLevel,
            'triggered_codes' => $triggeredCodes,
            'recommendation' => $recommendation,
            'is_critical' => $hasCriticalYes,
            'is_data_incomplete' => $hasSkipped,
        ]);
    }
}
