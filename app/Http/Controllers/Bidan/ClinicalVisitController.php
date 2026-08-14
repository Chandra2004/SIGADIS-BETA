<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Bidan\Concerns\AuthorizesPatientManagement;
use App\Http\Controllers\Bidan\Concerns\ScopesPatientsForWorker;
use App\Http\Controllers\Controller;
use App\Models\Pregnancy;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Catatan kunjungan klinis (Patient Screening Timeline & History View,
 * desain Figma) — pemeriksaan tatap muka oleh bidan, terpisah dari
 * screening_sessions (diisi ibu hamil sendiri lewat app).
 */
class ClinicalVisitController extends Controller
{
    use AuthorizesPatientManagement, ScopesPatientsForWorker;

    public function store(Request $request, Pregnancy $pregnancy): RedirectResponse
    {
        $worker = $this->authorizeManage($pregnancy);

        $data = $request->validate([
            'visit_type' => ['required', 'in:routine_screening,follow_up,other'],
            'status_tag' => ['required', 'in:normal,monitor,elevated'],
            'blood_pressure_systolic' => ['nullable', 'integer', 'min:0', 'max:300'],
            'blood_pressure_diastolic' => ['nullable', 'integer', 'min:0', 'max:200'],
            'symptoms' => ['nullable', 'array'],
            'symptoms.*' => ['string', 'max:100'],
            'clinical_notes' => ['nullable', 'string', 'max:4000'],
            'visited_at' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        $pregnancy->clinicalVisits()->create([
            'midwife_id' => $worker->id,
            'visit_type' => $data['visit_type'],
            'status_tag' => $data['status_tag'],
            'blood_pressure_systolic' => $data['blood_pressure_systolic'] ?? null,
            'blood_pressure_diastolic' => $data['blood_pressure_diastolic'] ?? null,
            'symptoms' => $data['symptoms'] ?? [],
            'clinical_notes' => $data['clinical_notes'] ?? null,
            'visited_at' => $data['visited_at'] ?? now(),
        ]);

        return back()->with('success', 'Catatan kunjungan ditambahkan.');
    }

    public function exportPdf(Pregnancy $pregnancy): HttpResponse
    {
        $worker = Auth::guard('staff')->user();
        abort_unless($this->patientsFor($worker)->whereKey($pregnancy->id)->exists(), 403);

        $pregnancy->load([
            'screeningSessions.riskAssessment',
            'referrals.facility',
            'clinicalVisits.midwife:id,full_name',
        ]);

        $pdf = Pdf::loadView('pdf.patient-history', ['pregnancy' => $pregnancy]);

        return $pdf->download('riwayat-pasien-'.$pregnancy->id.'.pdf');
    }
}
