<?php

namespace App\Http\Controllers;

use App\Actions\RegisterPregnancyAction;
use App\Http\Controllers\Concerns\ResolvesCurrentPregnancy;
use App\Http\Requests\RegisterPregnancyRequest;
use App\Models\HealthcareWorker;
use App\Models\MidwifeAssignment;
use App\Models\Pregnancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PregnancyController extends Controller
{
    use ResolvesCurrentPregnancy;

    public function showRegistrationForm(): Response
    {
        return Inertia::render('Kehamilan/Registrasi', [
            'consentVersion' => '1.0',
        ]);
    }

    /**
     * Fitur 1 (F.4 zonasi, §3.4.1): kandidat bidan default untuk region_code
     * tertentu, dipakai frontend untuk pratinjau sebelum submit final.
     */
    public function midwifeCandidates(Request $request)
    {
        $regionCode = $request->query('region_code', '');

        $midwives = HealthcareWorker::bidan()
            ->verified()
            ->available()
            ->inRegion($regionCode)
            ->get(['id', 'full_name', 'region_code']);

        return response()->json(['midwives' => $midwives]);
    }

    public function store(RegisterPregnancyRequest $request, RegisterPregnancyAction $action): RedirectResponse
    {
        $user = Auth::guard('pregnant')->user();

        $pregnancy = $action->handle($user, $request->validated());

        session(['active_pregnancy_id' => $pregnancy->id]);

        return redirect()->route('kehamilan.registrasi.sukses', $pregnancy);
    }

    /** Flows.md §3.5.4: layar sukses ringkas sebelum masuk Beranda. */
    public function registrationSuccess(Pregnancy $pregnancy): Response
    {
        abort_unless($pregnancy->pregnant_user_id === Auth::guard('pregnant')->id(), 403);
        $pregnancy->load('activeMidwifeAssignment.midwife');

        return Inertia::render('Kehamilan/RegistrasiSukses', [
            'motherName' => $pregnancy->mother_name,
            'gestationalAgeWeeks' => $pregnancy->currentGestationalAgeWeeks(),
            'estimatedDueDate' => $pregnancy->estimated_due_date,
            'midwifeName' => $pregnancy->activeMidwifeAssignment?->midwife?->full_name,
            'gpsPermissionEnabled' => (bool) Auth::guard('pregnant')->user()->gps_permission_enabled,
        ]);
    }

    public function beranda(): Response|RedirectResponse
    {
        $user = Auth::guard('pregnant')->user();
        $pregnancy = $this->currentPregnancy()
            ?->load('activeMidwifeAssignment.midwife', 'latestRiskAssessment')
            ->loadCount('screeningSessions');

        // Flows.md §13.4: layar transisi masa nifas tampil sekali begitu bidan
        // menandai persalinan, sebelum Beranda versi nifas biasa. Ditandai
        // lewat sesi (bukan kolom baru) -- cukup buat "sudah lihat sekali".
        if ($pregnancy && $pregnancy->status === 'nifas' && ! session()->has("nifas_transition_seen_{$pregnancy->id}")) {
            return redirect()->route('kehamilan.nifas.transisi');
        }

        return Inertia::render('Kehamilan/Beranda', [
            'motherName' => $user->full_name,
            'profilePhotoUrl' => $user->profilePhotoUrl(),
            'pregnancy' => $pregnancy ? [
                ...$pregnancy->toArray(),
                'current_gestational_age_weeks' => $pregnancy->currentGestationalAgeWeeks(),
                'progress_percent' => min(100, (int) round($pregnancy->currentGestationalAgeWeeks() / 40 * 100)),
            ] : null,
            'nextSessionType' => $pregnancy ? $this->nextSessionType($pregnancy) : null,
            // Switcher (Flows.md §16.2.1): cuma relevan kalau >1 profil.
            'allPregnancies' => $user->pregnancies()->active()->get(['id', 'mother_name']),
        ]);
    }

    /** Hub akun -- tujuan tab "Profil" di bottom nav, tidak ada frame Figma spesifik, mengikuti pola AccountDrawer. */
    public function profil(): Response
    {
        $user = Auth::guard('pregnant')->user();
        $pregnancy = $this->currentPregnancy();

        return Inertia::render('Kehamilan/Profil', [
            'motherName' => $user->full_name,
            'phoneNumber' => $user->phone_number,
            'profilePhotoUrl' => $user->profilePhotoUrl(),
            'hasActivePregnancy' => (bool) $pregnancy,
            'canChangeMidwife' => (bool) $pregnancy && $pregnancy->status !== 'case_closed',
        ]);
    }

    public function nifasTransition(): Response
    {
        $pregnancy = $this->currentPregnancy() ?? abort(404);
        abort_unless($pregnancy->status === 'nifas', 404);

        return Inertia::render('Kehamilan/TransisiNifas', [
            'motherName' => $pregnancy->mother_name,
        ]);
    }

    public function acknowledgeNifasTransition(): RedirectResponse
    {
        $pregnancy = $this->currentPregnancy() ?? abort(404);
        session()->put("nifas_transition_seen_{$pregnancy->id}", true);

        return redirect()->route('kehamilan.beranda');
    }

    /**
     * Flows.md §16.2.1: ganti profil kehamilan aktif di bawah nomor HP yang sama.
     */
    public function switchActive(int $pregnancy): RedirectResponse
    {
        $owned = Auth::guard('pregnant')->user()->pregnancies()->active()->whereKey($pregnancy)->exists();
        abort_unless($owned, 403);

        session(['active_pregnancy_id' => $pregnancy]);

        return redirect()->route('kehamilan.beranda');
    }

    /**
     * Flows.md §16.1: ganti bidan pendamping, pola sama dengan pairing di registrasi.
     */
    public function showChangeMidwife(): Response
    {
        $pregnancy = $this->currentPregnancy();

        return Inertia::render('Kehamilan/GantiBidan', [
            'regionCode' => $pregnancy->region_code,
            'currentMidwifeId' => $pregnancy->activeMidwifeAssignment?->midwife_id,
        ]);
    }

    public function changeMidwife(Request $request): RedirectResponse
    {
        $data = $request->validate(['midwife_id' => ['required', 'integer', 'exists:healthcare_workers,id']]);
        $pregnancy = $this->currentPregnancy();

        $pregnancy->activeMidwifeAssignment?->update(['is_active' => false, 'ended_at' => now()]);

        MidwifeAssignment::create([
            'pregnancy_id' => $pregnancy->id,
            'midwife_id' => $data['midwife_id'],
            'assignment_method' => 'manual_pilih',
            'is_active' => true,
            'started_at' => now(),
        ]);

        return redirect()->route('kehamilan.beranda')->with('success', 'Bidan pendamping berhasil diganti.');
    }

    protected function nextSessionType($pregnancy): ?string
    {
        return match ($pregnancy->status) {
            'case_closed' => null,
            'nifas' => 'nifas',
            default => $pregnancy->screeningSessions()->where('is_complete', true)->exists() ? 'periodic' : 'initial',
        };
    }
}
