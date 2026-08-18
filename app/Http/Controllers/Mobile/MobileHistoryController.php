<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Pregnancy;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MobileHistoryController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $user = Auth::guard('pregnant')->user();

        if (! $user) {
            return redirect()->route('mobile.login.show');
        }

        $activePregnancyId = session('active_pregnancy_id');
        $pregnancy = Pregnancy::with([
            'screeningSessions.screeningAnswers.screeningQuestion',
            'screeningSessions.riskAssessment',
            'riskAssessments',
            'clinicalVisits.midwife',
            'activeMidwifeAssignment.midwife',
        ])
            ->where('pregnant_user_id', $user->id)
            ->when($activePregnancyId, fn ($q) => $q->where('id', $activePregnancyId))
            ->first();

        if (! $pregnancy) {
            return redirect()->route('mobile.pregnancy.register.show');
        }

        // Susun riwayat sesi skrining
        $screeningHistory = $pregnancy->screeningSessions()
            ->with(['riskAssessment', 'screeningAnswers.screeningQuestion'])
            ->latest('started_at')
            ->get()
            ->map(function ($session) {
                $assessment = $session->riskAssessment;
                $answers = $session->screeningAnswers->map(fn ($ans) => [
                    'question' => $ans->screeningQuestion?->question_text ?? 'Gejala',
                    'category' => $ans->screeningQuestion?->category ?? 'umum',
                    'answer' => $ans->answer,
                ]);

                return [
                    'id' => $session->id,
                    'session_type' => $session->session_type,
                    'date' => Carbon::parse($session->completed_at ?? $session->started_at)->translatedFormat('d F Y, H:i'),
                    'risk_level' => $assessment?->risk_level ?? 'rendah',
                    'recommendation' => $assessment?->recommendation_text ?? 'Pemeriksaan selesai.',
                    'answers' => $answers,
                ];
            });

        // Susun riwayat pemeriksaan klinis fisik dari Bidan (ANC Log)
        $clinicalVisits = $pregnancy->clinicalVisits()
            ->with('midwife')
            ->latest('visited_at')
            ->get()
            ->map(fn ($v) => [
                'id' => $v->id,
                'date' => Carbon::parse($v->visited_at)->translatedFormat('d F Y'),
                'midwife_name' => $v->midwife?->full_name ?? 'Bidan Wilayah',
                'blood_pressure' => $v->systolic_bp ? "{$v->systolic_bp}/{$v->diastolic_bp} mmHg" : '-',
                'weight_kg' => $v->weight_kg ? "{$v->weight_kg} kg" : '-',
                'fetal_heart_rate' => $v->fetal_heart_rate ? "{$v->fetal_heart_rate} bpm" : '-',
                'clinical_notes' => $v->clinical_notes ?? '-',
            ]);

        return Inertia::render('Mobile/History', [
            'motherName' => $pregnancy->mother_name,
            'pregnancy' => [
                'id' => $pregnancy->id,
                'mother_name' => $pregnancy->mother_name,
                'status' => $pregnancy->status,
                'current_gestational_age_weeks' => $pregnancy->currentGestationalAgeWeeks(),
                'estimated_due_date' => $pregnancy->estimated_due_date ? Carbon::parse($pregnancy->estimated_due_date)->translatedFormat('d F Y') : null,
                'midwife_name' => $pregnancy->activeMidwifeAssignment?->midwife?->full_name ?? 'Belum ada Bidan',
            ],
            'screeningHistory' => $screeningHistory,
            'clinicalVisits' => $clinicalVisits,
        ]);
    }
}
