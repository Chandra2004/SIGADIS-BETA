<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\EmergencyAlert;
use App\Models\HealthcareWorker;
use App\Models\MidwifeAssignment;
use App\Models\Pregnancy;
use App\Models\RiskAssessment;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MobileDashboardController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $user = Auth::guard('pregnant')->user();

        if (! $user) {
            return redirect()->route('mobile.login.show');
        }

        // Cari kehamilan aktif yang dipilih dari session atau kehamilan aktif pertama
        $activePregnancyId = session('active_pregnancy_id');
        $pregnancy = null;

        if ($activePregnancyId) {
            $pregnancy = Pregnancy::with([
                'activeMidwifeAssignment.midwife',
                'latestRiskAssessment',
                'emergencyAlerts' => fn ($q) => $q->whereIn('status', ['pending', 'delivered', 'being_handled'])->latest(),
            ])
                ->where('pregnant_user_id', $user->id)
                ->where('id', $activePregnancyId)
                ->first();
        }

        if (! $pregnancy) {
            $pregnancy = Pregnancy::with([
                'activeMidwifeAssignment.midwife',
                'latestRiskAssessment',
                'emergencyAlerts' => fn ($q) => $q->whereIn('status', ['pending', 'delivered', 'being_handled'])->latest(),
            ])
                ->where('pregnant_user_id', $user->id)
                ->latest()
                ->first();

            if ($pregnancy) {
                session(['active_pregnancy_id' => $pregnancy->id]);
            }
        }

        // Layar transisi masa nifas tampil sekali saat baru masuk nifas (Flows.md §13.4)
        if ($pregnancy && $pregnancy->status === 'nifas' && ! session()->has("nifas_transition_seen_{$pregnancy->id}")) {
            if (\Illuminate\Support\Facades\Route::has('kehamilan.nifas.transisi')) {
                return redirect()->route('kehamilan.nifas.transisi');
            }
        }

        // Daftar seluruh kehamilan milik akun ini (untuk multi-profile switcher)
        $allPregnancies = Pregnancy::where('pregnant_user_id', $user->id)
            ->get(['id', 'mother_name', 'status', 'estimated_due_date', 'gestational_age_weeks_at_reg'])
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'mother_name' => $p->mother_name,
                    'status' => $p->status,
                    'gestational_age_weeks' => $p->currentGestationalAgeWeeks(),
                    'estimated_due_date' => $p->estimated_due_date ? Carbon::parse($p->estimated_due_date)->translatedFormat('d F Y') : null,
                ];
            });

        // Hitung data kehamilan aktif jika ada
        $pregnancyData = null;
        $activeAlert = null;

        if ($pregnancy) {
            $gestationalWeeks = $pregnancy->currentGestationalAgeWeeks();
            $trimester = match (true) {
                $gestationalWeeks <= 13 => 1,
                $gestationalWeeks <= 27 => 2,
                default => 3,
            };

            $nifasDay = 0;
            if ($pregnancy->status === 'nifas' && $pregnancy->nifas_started_at) {
                $nifasDay = min(42, max(1, Carbon::parse($pregnancy->nifas_started_at)->diffInDays(now()) + 1));
            }

            $midwife = $pregnancy->activeMidwifeAssignment?->midwife;

            $latestAlert = $pregnancy->emergencyAlerts->first();
            if ($latestAlert) {
                $activeAlert = [
                    'id' => $latestAlert->id,
                    'trigger_type' => $latestAlert->trigger_type,
                    'status' => $latestAlert->status,
                    'triggered_at' => Carbon::parse($latestAlert->triggered_at)->translatedFormat('d M Y, H:i'),
                    'handled_by_name' => $latestAlert->handledBy?->full_name ?? 'Bidan Wilayah',
                ];
            }

            $pregnancyData = [
                'id' => $pregnancy->id,
                'mother_name' => $pregnancy->mother_name,
                'status' => $pregnancy->status, // 'hamil', 'nifas', 'case_closed'
                'current_gestational_age_weeks' => $gestationalWeeks,
                'trimester' => $trimester,
                'progress_percent' => min(100, (int) round(($gestationalWeeks / 40) * 100)),
                'estimated_due_date' => $pregnancy->estimated_due_date ? Carbon::parse($pregnancy->estimated_due_date)->translatedFormat('d F Y') : null,
                'days_to_due_date' => $pregnancy->estimated_due_date ? max(0, now()->diffInDays(Carbon::parse($pregnancy->estimated_due_date), false)) : null,
                'nifas_day' => $nifasDay,
                'is_twin' => (bool) $pregnancy->is_twin_pregnancy,
                'has_prior_cesarean' => (bool) $pregnancy->has_prior_cesarean,
                'has_gestational_diabetes' => (bool) $pregnancy->has_gestational_diabetes,
                'has_chronic_hypertension' => (bool) $pregnancy->has_chronic_hypertension,
                'midwife' => $midwife ? [
                    'id' => $midwife->id,
                    'full_name' => $midwife->full_name,
                    'phone_number' => $midwife->phone_number,
                    'str_number' => $midwife->str_number,
                    'region_code' => $midwife->region_code,
                ] : null,
                'latest_risk' => $pregnancy->latestRiskAssessment ? [
                    'level' => $pregnancy->latestRiskAssessment->risk_level,
                    'created_at' => Carbon::parse($pregnancy->latestRiskAssessment->created_at)->translatedFormat('d M Y'),
                    'recommendation' => $pregnancy->latestRiskAssessment->recommendation_text,
                ] : null,
            ];
        }

        return Inertia::render('Mobile/Dashboard', [
            'motherName' => $user->full_name,
            'phoneNumber' => $user->phone_number,
            'profilePhotoUrl' => $user->profilePhotoUrl(),
            'pregnancy' => $pregnancyData,
            'allPregnancies' => $allPregnancies,
            'activeAlert' => $activeAlert,
        ]);
    }

    public function switchProfile(Request $request, Pregnancy $pregnancy): RedirectResponse
    {
        $user = Auth::guard('pregnant')->user();

        if ($pregnancy->pregnant_user_id !== $user->id) {
            abort(403, 'Akses tidak sah ke profil kehamilan.');
        }

        session(['active_pregnancy_id' => $pregnancy->id]);

        return redirect()->route('mobile.dashboard')->with('success', "Beralih ke profil Ibu {$pregnancy->mother_name}");
    }

    public function triggerEmergency(Request $request): RedirectResponse
    {
        $user = Auth::guard('pregnant')->user();
        $pregnancyId = session('active_pregnancy_id');

        $pregnancy = Pregnancy::where('pregnant_user_id', $user->id)
            ->when($pregnancyId, fn ($q) => $q->where('id', $pregnancyId))
            ->first();

        if (! $pregnancy) {
            return back()->withErrors(['error' => 'Profil kehamilan tidak ditemukan.']);
        }

        // Cek jika sudah ada alert darurat aktif agar tidak flooding
        $existingAlert = EmergencyAlert::where('pregnancy_id', $pregnancy->id)
            ->whereIn('status', ['pending', 'delivered', 'being_handled'])
            ->first();

        if ($existingAlert) {
            return back()->with('info', 'Peringatan darurat Anda sudah aktif dan sedang ditangani oleh petugas kesehatan.');
        }

        // Buat Risk Assessment darurat (Manual SOS)
        $riskAssessment = RiskAssessment::create([
            'pregnancy_id' => $pregnancy->id,
            'screening_session_id' => null,
            'risk_level' => 'tinggi',
            'triggered_rule_codes' => ['MANUAL_SOS_TRIGGER'],
            'recommendation_text' => 'Aktivasi tombol darurat manual. Bantuan segera dikirimkan.',
        ]);

        // Buat Emergency Alert
        $alert = EmergencyAlert::create([
            'pregnancy_id' => $pregnancy->id,
            'risk_assessment_id' => $riskAssessment->id,
            'trigger_type' => 'manual_button',
            'status' => 'pending',
            'triggered_at' => now(),
        ]);

        return redirect()->route('mobile.dashboard')->with('success', 'Peringatan darurat telah berhasil dikirimkan ke Bidan dan Kader pendamping!');
    }

    public function resolveEmergency(Request $request): RedirectResponse
    {
        $user = Auth::guard('pregnant')->user();
        $pregnancyId = session('active_pregnancy_id');

        $pregnancy = Pregnancy::where('pregnant_user_id', $user->id)
            ->when($pregnancyId, fn ($q) => $q->where('id', $pregnancyId))
            ->first();

        if ($pregnancy) {
            EmergencyAlert::where('pregnancy_id', $pregnancy->id)
                ->whereIn('status', ['pending', 'delivered', 'being_handled'])
                ->update([
                    'status' => 'resolved',
                    'resolved_at' => now(),
                ]);
        }

        return redirect()->route('mobile.dashboard')->with('success', 'Status darurat telah diselesaikan.');
    }
}
