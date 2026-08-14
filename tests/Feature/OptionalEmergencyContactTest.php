<?php

use App\Models\HealthcareWorker;
use App\Models\MidwifeAssignment;
use App\Models\PregnantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers a pregnancy without address or emergency contact (both optional)', function () {
    $user = PregnantUser::factory()->create();

    $this->actingAs($user, 'pregnant')->post(route('kehamilan.registrasi.store'), [
        'mother_name' => 'Ibu Tanpa Kontak', 'hpl_is_estimated' => true, 'gestational_age_weeks_at_registration' => 20,
        'is_twin_pregnancy' => false, 'has_prior_cesarean' => false, 'has_gestational_diabetes' => false,
        'has_chronic_hypertension' => false, 'region_code' => 'A', 'consent_granted' => true, 'consent_version' => '1.0',
    ])->assertRedirect();

    $pregnancy = $user->pregnancies()->first();
    expect($pregnancy->address)->toBeNull()
        ->and($pregnancy->emergency_contact_name)->toBeNull();
});

it('stores optional address and emergency contact when provided', function () {
    $user = PregnantUser::factory()->create();

    $this->actingAs($user, 'pregnant')->post(route('kehamilan.registrasi.store'), [
        'mother_name' => 'Ibu Dengan Kontak', 'hpl_is_estimated' => true, 'gestational_age_weeks_at_registration' => 20,
        'is_twin_pregnancy' => false, 'has_prior_cesarean' => false, 'has_gestational_diabetes' => false,
        'has_chronic_hypertension' => false, 'region_code' => 'A', 'consent_granted' => true, 'consent_version' => '1.0',
        'address' => 'Jl. Melati No. 5', 'emergency_contact_name' => 'Budi', 'emergency_contact_phone' => '081234567890',
    ])->assertRedirect();

    $pregnancy = $user->pregnancies()->first();
    expect($pregnancy->address)->toBe('Jl. Melati No. 5')
        ->and($pregnancy->emergency_contact_name)->toBe('Budi')
        ->and($pregnancy->emergency_contact_phone)->toBe('081234567890');
});

it('stores extended medical conditions and notes on registration', function () {
    $user = PregnantUser::factory()->create();

    $this->actingAs($user, 'pregnant')->post(route('kehamilan.registrasi.store'), [
        'mother_name' => 'Ibu Kondisi', 'hpl_is_estimated' => true, 'gestational_age_weeks_at_registration' => 20,
        'is_twin_pregnancy' => false, 'has_prior_cesarean' => false, 'has_gestational_diabetes' => false,
        'has_chronic_hypertension' => false, 'region_code' => 'A', 'consent_granted' => true, 'consent_version' => '1.0',
        'other_medical_conditions' => ['asthma', 'kidney_disorder'], 'medical_notes' => 'Riwayat asma sejak kecil.',
    ])->assertRedirect();

    $pregnancy = $user->pregnancies()->first();
    expect($pregnancy->other_medical_conditions)->toBe(['asthma', 'kidney_disorder'])
        ->and($pregnancy->medical_notes)->toBe('Riwayat asma sejak kecil.');
});

it('rejects an unrecognised medical condition key', function () {
    $user = PregnantUser::factory()->create();

    $this->actingAs($user, 'pregnant')->post(route('kehamilan.registrasi.store'), [
        'mother_name' => 'Ibu Invalid', 'hpl_is_estimated' => true, 'gestational_age_weeks_at_registration' => 20,
        'is_twin_pregnancy' => false, 'has_prior_cesarean' => false, 'has_gestational_diabetes' => false,
        'has_chronic_hypertension' => false, 'region_code' => 'A', 'consent_granted' => true, 'consent_version' => '1.0',
        'other_medical_conditions' => ['made_up_condition'],
    ])->assertSessionHasErrors('other_medical_conditions.0');
});

it('shows the emergency contact on the alert detail page when present', function () {
    $midwife = HealthcareWorker::factory()->create(['role' => 'bidan', 'status' => 'verified', 'region_code' => 'A']);
    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Kontak', 'gestational_age_weeks_at_registration' => 30, 'region_code' => 'A',
        'emergency_contact_name' => 'Siti', 'emergency_contact_phone' => '081298765432',
    ]);
    MidwifeAssignment::create([
        'pregnancy_id' => $pregnancy->id, 'midwife_id' => $midwife->id,
        'assignment_method' => 'auto_zonasi', 'is_active' => true, 'started_at' => now(),
    ]);
    $riskAssessment = $pregnancy->riskAssessments()->create([
        'risk_level' => 'tinggi', 'triggered_rule_codes' => [], 'recommendation_text' => '-', 'assessed_at' => now(),
    ]);
    $alert = $pregnancy->emergencyAlerts()->create([
        'trigger_type' => 'manual_button', 'risk_assessment_id' => $riskAssessment->id,
        'status' => 'pending', 'triggered_at' => now(),
    ]);

    $response = $this->actingAs($midwife, 'staff')->get(route('bidan.alerts.show', $alert));

    expect($response->viewData('page')['props']['alert']['pregnancy']['emergency_contact_name'])->toBe('Siti');
});
