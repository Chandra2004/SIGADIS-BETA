<?php

namespace App\Http\Controllers;

use App\Actions\ReactivateConsentAction;
use App\Actions\RequestDataDeletionAction;
use App\Actions\RevokeConsentAction;
use App\Http\Controllers\Concerns\ResolvesCurrentPregnancy;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Fitur "Privasi & Data Saya" (Flows.md §19). Cabut consent/hapus data
 * beroperasi per-profil kehamilan (pregnancy_id) — sesuai §19.3.6. Toggle
 * izin GPS/berbagi data & unduh data ada di level akun (pregnant_users),
 * tampil di layar yang sama tapi bukan bagian §19 dokumen sumber.
 */
class PrivacyController extends Controller
{
    use ResolvesCurrentPregnancy;

    public function index(): Response
    {
        $pregnancy = $this->currentPregnancy() ?? abort(404);
        $consent = $pregnancy->latestConsent;
        $user = Auth::guard('pregnant')->user();

        return Inertia::render('Kehamilan/Privasi', [
            'motherName' => $pregnancy->mother_name,
            'consentActive' => $pregnancy->hasActiveConsent(),
            'revokedAt' => $consent?->revoked_at,
            'deletionRequestedAt' => $consent?->data_deletion_requested_at,
            'gpsPermissionEnabled' => $user->gps_permission_enabled,
            'shareDataWithMidwifeEnabled' => $user->share_data_with_midwife_enabled,
        ]);
    }

    public function revokeConsent(RevokeConsentAction $action): RedirectResponse
    {
        $pregnancy = $this->currentPregnancy() ?? abort(404);
        $action->handle($pregnancy);

        return back()->with('success', 'Persetujuan dicabut. Skrining dan alert otomatis untuk profil ini dinonaktifkan.');
    }

    public function reactivateConsent(ReactivateConsentAction $action): RedirectResponse
    {
        $pregnancy = $this->currentPregnancy() ?? abort(404);
        $action->handle($pregnancy, '1.0');

        return back()->with('success', 'Persetujuan diaktifkan kembali.');
    }

    /** Flows.md §19.3.2: konfirmasi kuat, ketik ulang "HAPUS". */
    public function requestDeletion(Request $request, RequestDataDeletionAction $action): RedirectResponse
    {
        $request->validate(['confirmation' => ['required', 'in:HAPUS']]);

        $pregnancy = $this->currentPregnancy() ?? abort(404);
        $action->handle($pregnancy);

        return back()->with('success', 'Permintaan penghapusan diterima. Data Ibu akan diproses dalam beberapa hari kerja.');
    }

    public function updateGpsPermission(Request $request): RedirectResponse
    {
        $data = $request->validate(['enabled' => ['required', 'boolean']]);
        Auth::guard('pregnant')->user()->update(['gps_permission_enabled' => $data['enabled']]);

        return back()->with('success', 'Izin akses lokasi GPS diperbarui.');
    }

    /**
     * Catatan: toggle ini menyimpan preferensi pengguna, tapi TIDAK dipakai
     * buat menyembunyikan hasil skrining/alert dari bidan pendamping —
     * data keselamatan (risiko tinggi, alert darurat) tetap sampai ke
     * bidan tanpa syarat, sama prinsipnya dengan FAB darurat yang tetap
     * jalan meski consent dicabut (Flows.md §19.2.3). Kalau nanti dipakai
     * buat membatasi visibilitas data klinis, perlu didiskusikan ulang
     * trade-off keselamatan-nya dulu.
     */
    public function updateShareDataPermission(Request $request): RedirectResponse
    {
        $data = $request->validate(['enabled' => ['required', 'boolean']]);
        Auth::guard('pregnant')->user()->update(['share_data_with_midwife_enabled' => $data['enabled']]);

        return back()->with('success', 'Preferensi berbagi data diperbarui.');
    }

    /** "Unduh Salinan Data Saya (PDF)" — hak portabilitas data, per profil kehamilan aktif. */
    public function exportData(): HttpResponse
    {
        $pregnancy = $this->currentPregnancy() ?? abort(404);
        $pregnancy->load(['screeningSessions.riskAssessment', 'referrals.facility', 'consents']);

        $pdf = Pdf::loadView('pdf.data-export', ['pregnancy' => $pregnancy]);

        return $pdf->download('data-sigadis-'.$pregnancy->id.'.pdf');
    }
}
