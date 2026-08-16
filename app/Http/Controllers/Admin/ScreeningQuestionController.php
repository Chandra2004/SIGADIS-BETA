<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScreeningQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk Bank Soal & Protokol Skrining (Point 5).
 * Mengelola bank pertanyaan skrining mandiri ibu hamil, klasifikasi gejala kritis (Red Flag),
 * distribusi tipe sesi (Initial, Periodic, Nifas), serta audit tata kelola medis (Medical Governance).
 */
class ScreeningQuestionController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'category' => $request->query('category', 'semua'),
            'session_type' => $request->query('session_type', 'semua'),
            'critical_only' => $request->boolean('critical_only', false),
            'search' => trim((string) $request->query('search', '')),
        ];

        $questions = ScreeningQuestion::query()
            ->when($filters['category'] !== 'semua', fn ($q) => $q->where('category', $filters['category']))
            ->when($filters['session_type'] !== 'semua', fn ($q) => $q->whereJsonContains('applies_to_session_type', $filters['session_type']))
            ->when($filters['critical_only'], fn ($q) => $q->where('is_critical_symptom', true))
            ->when($filters['search'] !== '', fn ($q) => $q->where(fn ($q2) => $q2
                ->where('code', 'like', "%{$filters['search']}%")
                ->orWhere('question_text', 'like', "%{$filters['search']}%")
                ->orWhere('rule_reviewed_by', 'like', "%{$filters['search']}%")))
            ->orderBy('category')
            ->orderBy('id')
            ->get();

        // Metrik Ringkas Bank Soal
        $totalQuestions = ScreeningQuestion::count();
        $criticalCount = ScreeningQuestion::where('is_critical_symptom', true)->count();
        $initialCount = ScreeningQuestion::whereJsonContains('applies_to_session_type', 'initial')->count();
        $periodicCount = ScreeningQuestion::whereJsonContains('applies_to_session_type', 'periodic')->count();
        $nifasCount = ScreeningQuestion::whereJsonContains('applies_to_session_type', 'nifas')->count();
        $reviewedCount = ScreeningQuestion::whereNotNull('rule_reviewed_at')->count();

        return Inertia::render('Admin/BankSoal', [
            'questions' => $questions,
            'filters' => $filters,
            'metrics' => [
                'total' => $totalQuestions,
                'critical' => $criticalCount,
                'initial' => $initialCount,
                'periodic' => $periodicCount,
                'nifas' => $nifasCount,
                'reviewed' => $reviewedCount,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:screening_questions,code'],
            'question_text' => ['required', 'string', 'max:500'],
            'category' => ['required', Rule::in(['perdarahan', 'preeklamsia', 'infeksi', 'gerakan_janin', 'nyeri_perut', 'kejang', 'nifas_lain'])],
            'applies_to_session_type' => ['required', 'array', 'min:1'],
            'applies_to_session_type.*' => [Rule::in(['initial', 'periodic', 'nifas'])],
            'is_critical_symptom' => ['boolean'],
            'rule_reviewed_by' => ['nullable', 'string', 'max:100'],
        ]);

        if (! empty($data['rule_reviewed_by'])) {
            $data['rule_reviewed_at'] = now();
        }

        $question = ScreeningQuestion::create($data);

        return back()->with('success', "Pertanyaan [{$question->code}] berhasil ditambahkan ke bank soal skrining.");
    }

    public function update(Request $request, ScreeningQuestion $screeningQuestion): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('screening_questions', 'code')->ignore($screeningQuestion->id)],
            'question_text' => ['required', 'string', 'max:500'],
            'category' => ['required', Rule::in(['perdarahan', 'preeklamsia', 'infeksi', 'gerakan_janin', 'nyeri_perut', 'kejang', 'nifas_lain'])],
            'applies_to_session_type' => ['required', 'array', 'min:1'],
            'applies_to_session_type.*' => [Rule::in(['initial', 'periodic', 'nifas'])],
            'is_critical_symptom' => ['boolean'],
            'rule_reviewed_by' => ['nullable', 'string', 'max:100'],
        ]);

        if (! empty($data['rule_reviewed_by']) && $data['rule_reviewed_by'] !== $screeningQuestion->rule_reviewed_by) {
            $data['rule_reviewed_at'] = now();
        }

        $screeningQuestion->update($data);

        return back()->with('success', "Pertanyaan [{$screeningQuestion->code}] berhasil diperbarui.");
    }

    public function review(Request $request, ScreeningQuestion $screeningQuestion): RedirectResponse
    {
        $data = $request->validate([
            'reviewer_name' => ['required', 'string', 'max:100'],
        ]);

        $screeningQuestion->update([
            'rule_reviewed_by' => $data['reviewer_name'],
            'rule_reviewed_at' => now(),
        ]);

        return back()->with('success', "Protokol medis pertanyaan [{$screeningQuestion->code}] berhasil divalidasi oleh {$data['reviewer_name']}.");
    }

    public function destroy(ScreeningQuestion $screeningQuestion): RedirectResponse
    {
        $code = $screeningQuestion->code;
        $screeningQuestion->delete();

        return back()->with('success', "Pertanyaan [{$code}] berhasil dihapus dari bank soal.");
    }
}
