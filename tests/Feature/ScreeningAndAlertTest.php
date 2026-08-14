<?php

use App\Models\HealthcareWorker;
use App\Models\KaderAreaAssignment;
use App\Models\MidwifeAssignment;
use App\Models\PregnantUser;
use App\Models\ScreeningQuestion;
use App\Services\ScreeningNavigator;
use Database\Seeders\ScreeningQuestionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function pregnancyWithMidwife(): array
{
    $midwife = HealthcareWorker::factory()->create([
        'role' => 'bidan', 'status' => 'verified', 'region_code' => '33.08.05.2009',
    ]);
    $kader = HealthcareWorker::factory()->create([
        'role' => 'kader', 'status' => 'verified', 'region_code' => '33.08.05.2009',
    ]);
    KaderAreaAssignment::create(['kader_id' => $kader->id, 'region_code' => '33.08.05.2009', 'kader_priority' => 'primary']);

    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Skrining', 'gestational_age_weeks_at_registration' => 24, 'region_code' => '33.08.05.2009',
    ]);
    MidwifeAssignment::create([
        'pregnancy_id' => $pregnancy->id, 'midwife_id' => $midwife->id,
        'assignment_method' => 'auto_zonasi', 'is_active' => true, 'started_at' => now(),
    ]);

    return [$user, $pregnancy, $midwife, $kader];
}

it('completes a low-risk screening session and reaches result without an alert', function () {
    $this->seed(ScreeningQuestionSeeder::class);
    [$user, $pregnancy] = pregnancyWithMidwife();

    $this->actingAs($user, 'pregnant')
        ->post(route('skrining.mulai'), ['session_type' => 'initial'])
        ->assertRedirect();

    $session = $pregnancy->screeningSessions()->first();

    // Jawab semua pertanyaan applicable dengan "tidak"
    $questions = app(ScreeningNavigator::class)->applicableQuestions($pregnancy, 'initial');
    foreach ($questions as $q) {
        $this->actingAs($user, 'pregnant')
            ->post(route('skrining.jawab', $session), ['screening_question_id' => $q->id, 'answer' => 'tidak']);
    }

    // Memicu finalisasi: show() mendeteksi tidak ada pertanyaan tersisa lalu jalankan Risk Engine.
    $this->actingAs($user, 'pregnant')->get(route('skrining.show', $session))
        ->assertRedirect(route('skrining.hasil', $session));

    $response = $this->actingAs($user, 'pregnant')->get(route('skrining.hasil', $session->fresh()));
    $response->assertSuccessful();

    expect($pregnancy->riskAssessments()->first()->risk_level)->toBe('rendah')
        ->and($pregnancy->emergencyAlerts()->count())->toBe(0);
});

it('early-exits to tinggi on a critical answer and creates an emergency alert with recipients', function () {
    $this->seed(ScreeningQuestionSeeder::class);
    [$user, $pregnancy, $midwife, $kader] = pregnancyWithMidwife();

    $this->actingAs($user, 'pregnant')->post(route('skrining.mulai'), ['session_type' => 'initial']);
    $session = $pregnancy->screeningSessions()->first();

    $bleedingHeavy = ScreeningQuestion::where('code', 'bleeding_heavy')->firstOrFail();

    $this->actingAs($user, 'pregnant')
        ->post(route('skrining.jawab', $session), ['screening_question_id' => $bleedingHeavy->id, 'answer' => 'ya'])
        ->assertRedirect(route('skrining.hasil', $session));

    $riskAssessment = $pregnancy->riskAssessments()->first();
    expect($riskAssessment->risk_level)->toBe('tinggi');

    $alert = $pregnancy->emergencyAlerts()->first();
    expect($alert)->not->toBeNull()
        ->and($alert->trigger_type)->toBe('auto_risk_high')
        ->and($alert->recipients()->count())->toBe(2)
        ->and($alert->recipients()->where('healthcare_worker_id', $midwife->id)->exists())->toBeTrue()
        ->and($alert->recipients()->where('healthcare_worker_id', $kader->id)->exists())->toBeTrue();

    $response = $this->actingAs($user, 'pregnant')->get(route('skrining.hasil', $session->fresh()));
    expect($response->viewData('page')['props']['triggeredSymptoms'])->toBe([$bleedingHeavy->question_text]);
});

it('lets the midwife see and acknowledge the alert on the dashboard, race-safe against duplicate acknowledgement', function () {
    $this->seed(ScreeningQuestionSeeder::class);
    [$user, $pregnancy, $midwife, $kader] = pregnancyWithMidwife();

    $this->actingAs($user, 'pregnant')->post(route('skrining.mulai'), ['session_type' => 'initial']);
    $session = $pregnancy->screeningSessions()->first();
    $seizure = ScreeningQuestion::where('code', 'seizure')->firstOrFail();
    $this->actingAs($user, 'pregnant')->post(route('skrining.jawab', $session), ['screening_question_id' => $seizure->id, 'answer' => 'ya']);

    $alert = $pregnancy->emergencyAlerts()->first();

    $this->actingAs($midwife, 'staff')
        ->get(route('bidan.dashboard'))
        ->assertSuccessful();

    $this->actingAs($midwife, 'staff')
        ->post(route('bidan.alerts.acknowledge', $alert))
        ->assertRedirect();

    expect($alert->fresh()->status)->toBe('being_handled')
        ->and($alert->fresh()->handled_by_id)->toBe($midwife->id);

    // Penekan kedua yang SAH (kader wilayah yang sama) tidak bisa mengambil alih.
    $this->actingAs($kader, 'staff')->post(route('bidan.alerts.acknowledge', $alert));

    expect($alert->fresh()->handled_by_id)->toBe($midwife->id);
});

it('forbids a staff member with no assignment to the patient from viewing or acting on the alert', function () {
    $this->seed(ScreeningQuestionSeeder::class);
    [$user, $pregnancy] = pregnancyWithMidwife();

    $this->actingAs($user, 'pregnant')->post(route('skrining.mulai'), ['session_type' => 'initial']);
    $session = $pregnancy->screeningSessions()->first();
    $seizure = ScreeningQuestion::where('code', 'seizure')->firstOrFail();
    $this->actingAs($user, 'pregnant')->post(route('skrining.jawab', $session), ['screening_question_id' => $seizure->id, 'answer' => 'ya']);

    $alert = $pregnancy->emergencyAlerts()->first();
    $unrelatedWorker = HealthcareWorker::factory()->create(['status' => 'verified', 'region_code' => 'Z']);

    $this->actingAs($unrelatedWorker, 'staff')->get(route('bidan.alerts.show', $alert))->assertForbidden();
    $this->actingAs($unrelatedWorker, 'staff')->post(route('bidan.alerts.acknowledge', $alert))->assertForbidden();
    $this->actingAs($unrelatedWorker, 'staff')->get(route('bidan.alerts.history', $alert))->assertForbidden();
    $this->actingAs($unrelatedWorker, 'staff')->get(route('bidan.referrals.create', $alert))->assertForbidden();

    expect($alert->fresh()->status)->toBe('pending');
});

it('prevents manual activation from creating a duplicate open alert', function () {
    $this->seed(ScreeningQuestionSeeder::class);
    [$user, $pregnancy] = pregnancyWithMidwife();

    $this->actingAs($user, 'pregnant')->post(route('darurat.aktivasi'))->assertRedirect(route('darurat.status'));
    expect($pregnancy->emergencyAlerts()->count())->toBe(1);

    $this->actingAs($user, 'pregnant')->post(route('darurat.aktivasi'))->assertRedirect(route('darurat.status'));
    expect($pregnancy->emergencyAlerts()->count())->toBe(1);
});

it('shares the pregnant user tts_enabled preference on the screening question page', function () {
    $this->seed(ScreeningQuestionSeeder::class);
    [$user, $pregnancy] = pregnancyWithMidwife();
    $user->update(['tts_enabled' => false]);

    $this->actingAs($user, 'pregnant')->post(route('skrining.mulai'), ['session_type' => 'initial']);
    $session = $pregnancy->screeningSessions()->first();

    $response = $this->actingAs($user, 'pregnant')->get(route('skrining.show', $session));

    expect($response->viewData('page')['props']['ttsEnabled'])->toBeFalse();
});

it('lets the mother go back to the last answered question with her answer preselected', function () {
    $this->seed(ScreeningQuestionSeeder::class);
    [$user, $pregnancy] = pregnancyWithMidwife();

    $this->actingAs($user, 'pregnant')->post(route('skrining.mulai'), ['session_type' => 'initial']);
    $session = $pregnancy->screeningSessions()->first();
    $first = app(ScreeningNavigator::class)->applicableQuestions($pregnancy, 'initial')->first();

    $this->actingAs($user, 'pregnant')->post(route('skrining.jawab', $session), [
        'screening_question_id' => $first->id,
        'answer' => 'tidak',
    ]);

    $response = $this->actingAs($user, 'pregnant')->get(route('skrining.kembali', $session));
    $props = $response->viewData('page')['props'];

    expect($props['question']['id'])->toBe($first->id)
        ->and($props['currentAnswer'])->toBe('tidak')
        ->and($props['hasPreviousAnswer'])->toBeFalse();
});

it('updates the answer instead of duplicating it when re-answering via kembali', function () {
    $this->seed(ScreeningQuestionSeeder::class);
    [$user, $pregnancy] = pregnancyWithMidwife();

    $this->actingAs($user, 'pregnant')->post(route('skrining.mulai'), ['session_type' => 'initial']);
    $session = $pregnancy->screeningSessions()->first();
    $first = app(ScreeningNavigator::class)->applicableQuestions($pregnancy, 'initial')->first();

    $this->actingAs($user, 'pregnant')->post(route('skrining.jawab', $session), ['screening_question_id' => $first->id, 'answer' => 'tidak']);
    $this->actingAs($user, 'pregnant')->get(route('skrining.kembali', $session));
    $this->actingAs($user, 'pregnant')->post(route('skrining.jawab', $session), ['screening_question_id' => $first->id, 'answer' => 'ya']);

    expect($session->answers()->where('screening_question_id', $first->id)->where('is_superseded', false)->count())->toBe(1)
        ->and($session->answers()->where('screening_question_id', $first->id)->where('is_superseded', false)->first()->answer)->toBe('ya');
});

it('records used_text_to_speech when the mother played the question audio before answering', function () {
    $this->seed(ScreeningQuestionSeeder::class);
    [$user, $pregnancy] = pregnancyWithMidwife();

    $this->actingAs($user, 'pregnant')->post(route('skrining.mulai'), ['session_type' => 'initial']);
    $session = $pregnancy->screeningSessions()->first();
    $question = app(ScreeningNavigator::class)->applicableQuestions($pregnancy, 'initial')->first();

    $this->actingAs($user, 'pregnant')->post(route('skrining.jawab', $session), [
        'screening_question_id' => $question->id,
        'answer' => 'tidak',
        'used_text_to_speech' => true,
    ]);

    expect($session->answers()->where('screening_question_id', $question->id)->first()->used_text_to_speech)->toBeTrue();
});
