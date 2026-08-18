<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Pregnancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MobileSettingsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::guard('pregnant')->user();
        $activePregnancyId = session('active_pregnancy_id');

        $pregnancies = $user?->pregnancies()->latest()->get() ?? collect();
        $activePregnancy = $pregnancies->firstWhere('id', $activePregnancyId) ?? $pregnancies->first();

        return Inertia::render('Mobile/Settings', [
            'user' => [
                'id' => $user?->id,
                'full_name' => $user?->full_name,
                'phone_number' => $user?->phone_number,
                'profile_photo_url' => $user?->profilePhotoUrl(),
                'text_size' => $user?->text_size ?? 'normal', // 'normal', 'besar'
                'tts_enabled' => (bool) ($user?->tts_enabled ?? true),
                'screening_reminder_enabled' => (bool) ($user?->screening_reminder_enabled ?? true),
                'language' => 'id',
            ],
            'pregnancies' => $pregnancies->map(fn (Pregnancy $p) => [
                'id' => $p->id,
                'mother_name' => $p->mother_name,
                'status' => $p->status,
                'gestational_age_weeks' => $p->currentGestationalAgeWeeks(),
                'estimated_due_date' => $p->estimated_due_date?->translatedFormat('d F Y'),
                'is_active' => $p->id === $activePregnancy?->id,
            ]),
            'activePregnancy' => $activePregnancy ? [
                'id' => $activePregnancy->id,
                'mother_name' => $activePregnancy->mother_name,
                'status' => $activePregnancy->status,
                'trimester' => $activePregnancy->trimester,
                'gestational_age_weeks' => $activePregnancy->currentGestationalAgeWeeks(),
                'estimated_due_date' => $activePregnancy->estimated_due_date?->translatedFormat('d F Y'),
            ] : null,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var \App\Models\PregnantUser|null $user */
        $user = Auth::guard('pregnant')->user();

        $validated = $request->validate([
            'text_size' => ['nullable', 'string', 'in:normal,besar'],
            'tts_enabled' => ['nullable', 'boolean'],
            'screening_reminder_enabled' => ['nullable', 'boolean'],
        ]);

        if ($user && ! empty($validated)) {
            $user->update(array_filter($validated, fn ($val) => ! is_null($val)));
        }

        return back()->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
