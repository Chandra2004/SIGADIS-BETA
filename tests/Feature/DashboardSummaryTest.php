<?php

use App\Models\HealthcareWorker;
use App\Models\MidwifeAssignment;
use App\Models\PregnantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function patientWithRisk(HealthcareWorker $midwife, string $motherName, ?string $riskLevel, string $status = 'hamil'): void
{
    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => $motherName, 'gestational_age_weeks_at_registration' => 30, 'region_code' => 'A', 'status' => $status,
    ]);
    MidwifeAssignment::create([
        'pregnancy_id' => $pregnancy->id, 'midwife_id' => $midwife->id,
        'assignment_method' => 'auto_zonasi', 'is_active' => true, 'started_at' => now(),
    ]);

    if ($riskLevel) {
        $pregnancy->riskAssessments()->create([
            'risk_level' => $riskLevel, 'triggered_rule_codes' => [],
            'recommendation_text' => '-', 'assessed_at' => now(),
        ]);
    }
}

it('summarizes patient counts by risk level and nifas status', function () {
    $midwife = HealthcareWorker::factory()->create(['role' => 'bidan', 'status' => 'verified', 'region_code' => 'A']);
    patientWithRisk($midwife, 'Ibu Tinggi', 'tinggi');
    patientWithRisk($midwife, 'Ibu Sedang', 'sedang');
    patientWithRisk($midwife, 'Ibu Rendah', 'rendah');
    patientWithRisk($midwife, 'Ibu Nifas', null, 'nifas');

    $response = $this->actingAs($midwife, 'staff')->get(route('bidan.dashboard'));
    $response->assertSuccessful();
    $summary = $response->viewData('page')['props']['summary'];

    expect($summary)->toBe(['total' => 4, 'risiko_tinggi' => 1, 'risiko_sedang' => 1, 'nifas' => 1]);
});

it('filters the patient table by risk level via query param', function () {
    $midwife = HealthcareWorker::factory()->create(['role' => 'bidan', 'status' => 'verified', 'region_code' => 'A']);
    patientWithRisk($midwife, 'Ibu Tinggi', 'tinggi');
    patientWithRisk($midwife, 'Ibu Rendah', 'rendah');

    $response = $this->actingAs($midwife, 'staff')->get(route('bidan.dashboard', ['filter' => 'tinggi']));
    $patients = $response->viewData('page')['props']['patients'];

    expect($patients)->toHaveCount(1)
        ->and($patients[0]['mother_name'])->toBe('Ibu Tinggi');
});
