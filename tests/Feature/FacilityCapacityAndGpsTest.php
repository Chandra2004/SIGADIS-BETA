<?php

use App\Models\Facility;
use App\Models\HealthcareWorker;
use App\Models\MidwifeAssignment;
use App\Models\PregnantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('captures GPS coordinates on the emergency alert when provided', function () {
    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu GPS', 'gestational_age_weeks_at_registration' => 30, 'region_code' => 'A',
    ]);

    $this->actingAs($user, 'pregnant')
        ->post(route('darurat.aktivasi'), ['latitude' => -6.1751, 'longitude' => 106.8272])
        ->assertRedirect();

    $alert = $pregnancy->emergencyAlerts()->first();
    expect((float) $alert->latitude)->toBe(-6.1751)
        ->and((float) $alert->longitude)->toBe(106.8272);
});

it('activates the emergency alert fine without GPS coordinates (permission denied)', function () {
    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Tanpa GPS', 'gestational_age_weeks_at_registration' => 30, 'region_code' => 'A',
    ]);

    $this->actingAs($user, 'pregnant')->post(route('darurat.aktivasi'))->assertRedirect();

    expect($pregnancy->emergencyAlerts()->first()->latitude)->toBeNull();
});

it('filters referral facilities by ICU/NICU and sorts by distance from the alert location', function () {
    $midwife = HealthcareWorker::factory()->create(['role' => 'bidan', 'status' => 'verified', 'region_code' => 'A']);
    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Rujuk', 'gestational_age_weeks_at_registration' => 30, 'region_code' => 'A',
    ]);
    MidwifeAssignment::create([
        'pregnancy_id' => $pregnancy->id, 'midwife_id' => $midwife->id,
        'assignment_method' => 'auto_zonasi', 'is_active' => true, 'started_at' => now(),
    ]);

    $near = Facility::create([
        'name' => 'RS Dekat', 'type' => 'rumah_sakit', 'region_code' => 'A', 'address' => '-',
        'latitude' => -6.1800, 'longitude' => 106.8300, 'has_icu' => true, 'has_nicu' => true,
    ]);
    $far = Facility::create([
        'name' => 'RS Jauh', 'type' => 'rumah_sakit', 'region_code' => 'A', 'address' => '-',
        'latitude' => -6.9000, 'longitude' => 107.6000, 'has_icu' => true, 'has_nicu' => false,
    ]);

    $riskAssessment = $pregnancy->riskAssessments()->create([
        'risk_level' => 'tinggi', 'triggered_rule_codes' => ['bleeding_heavy'],
        'recommendation_text' => 'Segera ke faskes.', 'assessed_at' => now(),
    ]);
    $alert = $pregnancy->emergencyAlerts()->create([
        'trigger_type' => 'manual_button', 'risk_assessment_id' => $riskAssessment->id,
        'status' => 'pending', 'triggered_at' => now(), 'latitude' => -6.1751, 'longitude' => 106.8272,
    ]);

    $response = $this->actingAs($midwife, 'staff')->get(route('bidan.referrals.create', $alert));
    $response->assertSuccessful();
    $facilities = $response->viewData('page')['props']['facilities'];
    expect($facilities[0]['name'])->toBe('RS Dekat');

    $filtered = $this->actingAs($midwife, 'staff')
        ->get(route('bidan.referrals.create', $alert).'?has_nicu=1')
        ->viewData('page')['props']['facilities'];
    expect($filtered)->toHaveCount(1)
        ->and($filtered[0]['name'])->toBe('RS Dekat');
});
