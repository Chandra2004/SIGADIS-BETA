<?php

namespace App\Http\Controllers\Mobile;

use App\Actions\ReactivateConsentAction;
use App\Actions\RevokeConsentAction;
use App\Http\Controllers\Controller;
use App\Models\Consent;
use App\Models\Pregnancy;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MobilePrivacyController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::guard('pregnant')->user();
        $activePregnancyId = session('active_pregnancy_id');

        $pregnancy = Pregnancy::where('pregnant_user_id', $user?->id)
            ->when($activePregnancyId, fn ($q) => $q->where('id', $activePregnancyId))
            ->first();

        $consent = null;
        if ($pregnancy) {
            $consentRecord = $pregnancy->latestConsent;
            if ($consentRecord) {
                $consent = [
                    'version' => $consentRecord->consent_version ?? '1.0',
                    'granted_at' => $consentRecord->granted_at ? Carbon::parse($consentRecord->granted_at)->translatedFormat('d F Y, H:i') : null,
                    'is_revoked' => ! is_null($consentRecord->revoked_at),
                    'revoked_at' => $consentRecord->revoked_at ? Carbon::parse($consentRecord->revoked_at)->translatedFormat('d F Y, H:i') : null,
                    'deletion_requested' => ! is_null($consentRecord->data_deletion_requested_at),
                    'deletion_requested_at' => $consentRecord->data_deletion_requested_at ? Carbon::parse($consentRecord->data_deletion_requested_at)->translatedFormat('d F Y, H:i') : null,
                ];
            }
        }

        return Inertia::render('Mobile/Privacy', [
            'motherName' => $pregnancy?->mother_name ?? $user?->full_name,
            'consent' => $consent,
            'consentActive' => (bool) ($pregnancy?->hasActiveConsent() ?? true),
        ]);
    }

    public function revokeConsent(Request $request, RevokeConsentAction $action): RedirectResponse
    {
        $user = Auth::guard('pregnant')->user();
        $activePregnancyId = session('active_pregnancy_id');

        $pregnancy = Pregnancy::where('pregnant_user_id', $user?->id)
            ->when($activePregnancyId, fn ($q) => $q->where('id', $activePregnancyId))
            ->first();

        if ($pregnancy) {
            $action->handle($pregnancy);
        }

        return back()->with('info', 'Persetujuan pemrosesan data telah dicabut. Skrining mandiri rutin dinonaktifkan.');
    }

    public function reactivateConsent(Request $request, ReactivateConsentAction $action): RedirectResponse
    {
        $user = Auth::guard('pregnant')->user();
        $activePregnancyId = session('active_pregnancy_id');

        $pregnancy = Pregnancy::where('pregnant_user_id', $user?->id)
            ->when($activePregnancyId, fn ($q) => $q->where('id', $activePregnancyId))
            ->first();

        if ($pregnancy) {
            $action->handle($pregnancy, '1.0');
        }

        return back()->with('success', 'Persetujuan pemrosesan data telah diaktifkan kembali. Anda dapat kembali mengisi skrining mandiri.');
    }

    public function requestDeletion(Request $request): RedirectResponse
    {
        $request->validate([
            'confirmation' => ['required', 'string', 'in:HAPUS'],
        ]);

        $user = Auth::guard('pregnant')->user();

        if ($user) {
            // Tandai deletion requested dan cabut consent pada seluruh kehamilan
            foreach ($user->pregnancies as $pregnancy) {
                $pregnancy->latestConsent?->update([
                    'data_deletion_requested_at' => now(),
                    'revoked_at' => now(),
                ]);
            }

            // Hapus profil kehamilan dan akun ibu hamil (self-deletion)
            $user->pregnancies()->delete();
            $user->delete();

            // Logout sesi secara penuh
            Auth::guard('pregnant')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('mobile.login.show')->with('status', 'Akun dan seluruh data pribadi Anda telah berhasil dihapus dari sistem SIGADIS.');
        }

        return redirect()->route('mobile.login.show');
    }
}
