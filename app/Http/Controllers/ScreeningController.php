<?php

namespace App\Http\Controllers;

use App\Actions\FinalizeScreeningSessionAction;
use App\Http\Controllers\Concerns\ResolvesCurrentPregnancy;
use App\Http\Requests\AnswerScreeningQuestionRequest;
use App\Models\ScreeningQuestion;
use App\Models\ScreeningSession;
use App\Services\RiskAssessmentEngine;
use App\Services\ScreeningNavigator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ScreeningController extends Controller
{
    use ResolvesCurrentPregnancy;

    public function __construct(
        protected ScreeningNavigator $navigator,
        protected RiskAssessmentEngine $engine,
        protected FinalizeScreeningSessionAction $finalize,
    ) {}

    /** Flows.md §4.1.2: layar transisi singkat sebelum pertanyaan pertama sesi baru. */
    public function transition(Request $request): Response
    {
        $data = $request->validate([
            'session_type' => ['required', Rule::in(['initial', 'periodic', 'nifas'])],
        ]);

        return Inertia::render('Skrining/Transisi', ['sessionType' => $data['session_type']]);
    }

    public function start(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'session_type' => ['required', Rule::in(['initial', 'periodic', 'nifas'])],
        ]);

        $pregnancy = $this->currentPregnancy() ?? abort(404);

        // Flows.md §19.2.3: consent dicabut -> skrining baru diblokir. FAB
        // darurat (EmergencyAlertController::activate) sengaja tetap jalan,
        // keselamatan tidak boleh diblokir walau consent pemrosesan data
        // rutin sudah dicabut.
        abort_unless($pregnancy->hasActiveConsent(), 403, 'Persetujuan Ibu untuk profil ini sudah dicabut. Aktifkan kembali lewat menu Privasi & Data Saya untuk mulai skrining.');

        $session = $pregnancy->screeningSessions()->create([
            'session_type' => $data['session_type'],
            'started_at' => now(),
        ]);

        return redirect()->route('skrining.show', $session);
    }

    public function show(ScreeningSession $session): Response|RedirectResponse
    {
        $this->authorizeSession($session);

        if ($session->is_complete) {
            return redirect()->route('skrining.hasil', $session);
        }

        $answeredCodes = $this->activeAnsweredCodes($session);
        $question = $this->navigator->nextQuestion($session->pregnancy, $session->session_type, $answeredCodes);

        if (! $question) {
            $this->finalize->handle($session);

            return redirect()->route('skrining.hasil', $session);
        }

        return $this->renderQuestion($session, $question);
    }

    /**
     * Flows.md §4.2.3a/§4.2.4: tombol "Pertanyaan Sebelumnya" -- pertanyaan
     * terakhir yang sudah terjawab (non-superseded) di sesi ini SELALU sama
     * dengan "pertanyaan sebelumnya" pada urutan yang baru saja dilalui,
     * karena decision tree dinamis tidak punya urutan tetap untuk dilacak
     * terpisah. Menjawab ulang lewat layar ini memakai endpoint jawab() yang
     * sama (supersede + insert baru), lalu show() menghitung ulang pertanyaan
     * berikutnya dari state terkini -- otomatis pindah cabang kalau jawaban
     * berubah, tanpa logika tambahan.
     */
    public function back(ScreeningSession $session): Response
    {
        $this->authorizeSession($session);

        $lastAnswer = $session->answers()->where('is_superseded', false)->latest('answered_at')->first();
        abort_unless($lastAnswer, 404);

        return $this->renderQuestion($session, $lastAnswer->question, $lastAnswer->answer);
    }

    public function answer(AnswerScreeningQuestionRequest $request, ScreeningSession $session): RedirectResponse
    {
        $this->authorizeSession($session);

        $data = $request->validated();

        $session->answers()->where('screening_question_id', $data['screening_question_id'])->update(['is_superseded' => true]);

        $session->answers()->create([
            'screening_question_id' => $data['screening_question_id'],
            'answer' => $data['answer'],
            'answered_at' => now(),
            'used_text_to_speech' => $data['used_text_to_speech'] ?? false,
        ]);

        if ($this->shouldEarlyExit($session)) {
            $this->finalize->handle($session);

            return redirect()->route('skrining.hasil', $session);
        }

        return redirect()->route('skrining.show', $session);
    }

    public function skip(Request $request, ScreeningSession $session): RedirectResponse
    {
        $this->authorizeSession($session);

        // Melewati pertanyaan (Flows.md §4.3.1): sengaja tidak membuat row
        // screening_answers, lanjut ke pertanyaan berikutnya.
        return redirect()->route('skrining.show', $session);
    }

    public function hasil(ScreeningSession $session): Response
    {
        $this->authorizeSession($session);

        $riskAssessment = $session->riskAssessment;

        // Flows.md §5: breakdown gejala yang memicu hasil -- triggered_rule_codes berisi
        // ScreeningQuestion.code (kecuali 'manual_activation' dari aktivasi SOS manual).
        $triggeredSymptoms = $riskAssessment
            ? ScreeningQuestion::query()->whereIn('code', $riskAssessment->triggered_rule_codes)->pluck('question_text')->all()
            : [];

        return Inertia::render('Skrining/Hasil', [
            'riskAssessment' => $riskAssessment,
            'alertSent' => $riskAssessment && $riskAssessment->risk_level === 'tinggi',
            'triggeredSymptoms' => $triggeredSymptoms,
        ]);
    }

    protected function renderQuestion(ScreeningSession $session, ScreeningQuestion $question, ?string $currentAnswer = null): Response
    {
        $answeredCodes = $this->activeAnsweredCodes($session)->reject(fn ($code) => $code === $question->code)->values();
        $total = $this->navigator->applicableQuestions($session->pregnancy, $session->session_type)->count();

        return Inertia::render('Skrining/Pertanyaan', [
            'session' => $session->only('id', 'session_type'),
            'question' => $question->only('id', 'question_text', 'category'),
            'progress' => [
                'answered' => $answeredCodes->count(),
                'total' => $total,
            ],
            // Flows.md §29.4.1: toggle global, dipakai cuma di layar ini.
            'ttsEnabled' => (bool) Auth::guard('pregnant')->user()->tts_enabled,
            'hasPreviousAnswer' => $answeredCodes->isNotEmpty(),
            'currentAnswer' => $currentAnswer,
        ]);
    }

    protected function activeAnsweredCodes(ScreeningSession $session)
    {
        return $session->answers()->where('is_superseded', false)->with('question')->get()->map(fn ($a) => $a->question->code);
    }

    protected function shouldEarlyExit(ScreeningSession $session): bool
    {
        $answers = $session->answers()->where('is_superseded', false)->with('question')->get()
            ->mapWithKeys(fn ($a) => [$a->question->code => $a->answer]);

        return $this->engine->evaluate($session->pregnancy, $answers)['risk_level'] === 'tinggi';
    }

    protected function authorizeSession(ScreeningSession $session): void
    {
        abort_unless($session->pregnancy->pregnant_user_id === Auth::guard('pregnant')->id(), 403);
    }
}
