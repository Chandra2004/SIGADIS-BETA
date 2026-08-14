<?php

use App\Models\HealthcareWorker;
use App\Models\MidwifeAssignment;
use App\Models\PregnantUser;
use App\Models\ScreeningQuestion;
use Database\Seeders\ScreeningQuestionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows human-readable symptom labels instead of raw rule codes on the alert detail page', function () {
    $this->seed(ScreeningQuestionSeeder::class);

    $midwife = HealthcareWorker::factory()->create(['role' => 'bidan', 'status' => 'verified', 'region_code' => 'A']);
    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Label', 'gestational_age_weeks_at_registration' => 30, 'region_code' => 'A',
    ]);
    MidwifeAssignment::create([
        'pregnancy_id' => $pregnancy->id, 'midwife_id' => $midwife->id,
        'assignment_method' => 'auto_zonasi', 'is_active' => true, 'started_at' => now(),
    ]);

    $question = ScreeningQuestion::where('code', 'bleeding_heavy')->firstOrFail();
    $riskAssessment = $pregnancy->riskAssessments()->create([
        'risk_level' => 'tinggi', 'triggered_rule_codes' => ['bleeding_heavy'],
        'recommendation_text' => 'Segera ke faskes.', 'assessed_at' => now(),
    ]);
    $alert = $pregnancy->emergencyAlerts()->create([
        'trigger_type' => 'auto_risk_high', 'risk_assessment_id' => $riskAssessment->id,
        'status' => 'pending', 'triggered_at' => now(),
    ]);

    $response = $this->actingAs($midwife, 'staff')->get(route('bidan.alerts.show', $alert));

    expect($response->viewData('page')['props']['alert']['triggered_symptoms'])->toBe([$question->question_text]);
});

it('shows a manual-activation label instead of a raw code', function () {
    $midwife = HealthcareWorker::factory()->create(['role' => 'bidan', 'status' => 'verified', 'region_code' => 'A']);
    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu SOS', 'gestational_age_weeks_at_registration' => 30, 'region_code' => 'A',
    ]);
    MidwifeAssignment::create([
        'pregnancy_id' => $pregnancy->id, 'midwife_id' => $midwife->id,
        'assignment_method' => 'auto_zonasi', 'is_active' => true, 'started_at' => now(),
    ]);

    $riskAssessment = $pregnancy->riskAssessments()->create([
        'risk_level' => 'tinggi', 'triggered_rule_codes' => ['manual_activation'],
        'recommendation_text' => 'Peringatan darurat diaktifkan manual oleh Ibu.', 'assessed_at' => now(),
    ]);
    $alert = $pregnancy->emergencyAlerts()->create([
        'trigger_type' => 'manual_button', 'risk_assessment_id' => $riskAssessment->id,
        'status' => 'pending', 'triggered_at' => now(),
    ]);

    $response = $this->actingAs($midwife, 'staff')->get(route('bidan.alerts.show', $alert));

    expect($response->viewData('page')['props']['alert']['triggered_symptoms'])->toBe(['Diaktifkan manual oleh Ibu (tombol SOS)']);
});
