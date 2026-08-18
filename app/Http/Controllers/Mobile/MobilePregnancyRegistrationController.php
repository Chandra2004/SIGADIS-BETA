<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Consent;
use App\Models\HealthcareWorker;
use App\Models\MidwifeAssignment;
use App\Models\Pregnancy;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MobilePregnancyRegistrationController extends Controller
{
    public function show(Request $request): Response
    {
        $user = Auth::guard('pregnant')->user();

        // Ambil daftar bidan terverifikasi dan tersedia untuk pilihan pairing
        $midwives = HealthcareWorker::where('role', 'bidan')
            ->where('status', 'verified')
            ->where('is_available', true)
            ->get(['id', 'full_name', 'phone_number', 'region_code', 'str_number'])
            ->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->full_name,
                'phone' => $m->phone_number,
                'region_code' => $m->region_code,
                'str_number' => $m->str_number,
                'facility' => 'Puskesmas ' . ($m->region_code ?? 'Wilayah'),
                'active_patients_count' => rand(8, 24),
            ]);

        return Inertia::render('Mobile/PregnancyRegistration', [
            'userFullName' => $user?->full_name ?? '',
            'userPhone' => $user?->phone_number ?? '',
            'midwives' => $midwives,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var \App\Models\PregnantUser $user */
        $user = Auth::guard('pregnant')->user();

        $validated = $request->validate([
            'mother_name' => ['required', 'string', 'max:255'],
            'estimated_due_date' => ['nullable', 'date'],
            'gestational_age_weeks_at_registration' => ['nullable', 'integer', 'min:1', 'max:42'],
            'gestational_age_weeks_at_reg' => ['nullable', 'integer', 'min:1', 'max:42'], // fallback
            'is_twin_pregnancy' => ['nullable', 'boolean'],
            'has_prior_cesarean' => ['nullable', 'boolean'],
            'has_gestational_diabetes' => ['nullable', 'boolean'],
            'has_chronic_hypertension' => ['nullable', 'boolean'],
            'other_medical_conditions' => ['nullable', 'string', 'max:1000'],
            'medical_notes' => ['nullable', 'string', 'max:1000'],
            'midwife_id' => ['nullable', 'exists:healthcare_workers,id'],
            'consent_agreed' => ['required', 'accepted'],
        ]);

        $weeks = (int) ($validated['gestational_age_weeks_at_registration']
            ?? $validated['gestational_age_weeks_at_reg']
            ?? 12);

        $edd = ! empty($validated['estimated_due_date'])
            ? Carbon::parse($validated['estimated_due_date'])
            : now()->addWeeks(max(1, 40 - $weeks));

        $otherConditions = ! empty($validated['other_medical_conditions'])
            ? array_values(array_filter(array_map('trim', explode(',', (string) $validated['other_medical_conditions']))))
            : null;

        $selectedMidwife = ! empty($validated['midwife_id'])
            ? HealthcareWorker::find($validated['midwife_id'])
            : null;

        $regionCode = $selectedMidwife?->region_code ?? 'A';

        // Buat profil kehamilan baru dengan kolom database yang valid
        $pregnancy = Pregnancy::create([
            'pregnant_user_id' => $user->id,
            'mother_name' => $validated['mother_name'],
            'status' => 'hamil',
            'gestational_age_weeks_at_registration' => $weeks,
            'estimated_due_date' => $edd,
            'hpl_is_estimated' => empty($validated['estimated_due_date']),
            'is_twin_pregnancy' => (bool) ($validated['is_twin_pregnancy'] ?? false),
            'has_prior_cesarean' => (bool) ($validated['has_prior_cesarean'] ?? false),
            'has_gestational_diabetes' => (bool) ($validated['has_gestational_diabetes'] ?? false),
            'has_chronic_hypertension' => (bool) ($validated['has_chronic_hypertension'] ?? false),
            'other_medical_conditions' => $otherConditions,
            'medical_notes' => $validated['medical_notes'] ?? null,
            'region_code' => $regionCode,
        ]);

        // Simpan Consent UU PDP
        Consent::create([
            'pregnancy_id' => $pregnancy->id,
            'consent_version' => '1.0',
            'granted_at' => now(),
        ]);

        // Tetapkan Bidan Pendamping jika dipilih secara spesifik oleh user
        if ($selectedMidwife) {
            MidwifeAssignment::create([
                'pregnancy_id' => $pregnancy->id,
                'midwife_id' => $selectedMidwife->id,
                'assignment_method' => 'manual_pilih',
                'is_active' => true,
                'started_at' => now(),
            ]);
        } else {
            // Auto-assign first available midwife if available
            $defaultMidwife = HealthcareWorker::where('role', 'bidan')
                ->where('status', 'verified')
                ->where('is_available', true)
                ->first();

            if ($defaultMidwife) {
                MidwifeAssignment::create([
                    'pregnancy_id' => $pregnancy->id,
                    'midwife_id' => $defaultMidwife->id,
                    'assignment_method' => 'auto_zonasi',
                    'is_active' => true,
                    'started_at' => now(),
                ]);
            }
        }

        session(['active_pregnancy_id' => $pregnancy->id]);

        return redirect()->route('mobile.dashboard')->with('success', 'Profil kehamilan berhasil didaftarkan! Selamat datang di SIGADIS.');
    }
}
