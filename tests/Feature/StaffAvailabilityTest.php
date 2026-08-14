<?php

use App\Console\Commands\ReactivateExpiredAvailability;
use App\Models\HealthcareWorker;
use App\Models\KaderAreaAssignment;
use App\Models\MidwifeAssignment;
use App\Models\PregnantUser;
use App\Services\EmergencyAlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lets a worker deactivate and reactivate their own availability', function () {
    $worker = HealthcareWorker::factory()->create(['status' => 'verified']);

    $this->actingAs($worker, 'staff')
        ->post(route('bidan.availability.deactivate'), [
            'unavailable_from' => now()->addDay()->toDateString(),
            'unavailable_until' => now()->addDays(5)->toDateString(),
        ])
        ->assertRedirect();

    $fresh = $worker->fresh();
    expect($fresh->is_available)->toBeFalse()
        ->and($fresh->unavailable_from)->not->toBeNull()
        ->and($fresh->unavailable_until)->not->toBeNull();

    $this->actingAs($worker, 'staff')
        ->post(route('bidan.availability.reactivate'))
        ->assertRedirect();

    $fresh = $worker->fresh();
    expect($fresh->is_available)->toBeTrue()
        ->and($fresh->unavailable_from)->toBeNull()
        ->and($fresh->unavailable_until)->toBeNull();
});

it('excludes an unavailable primary kader from new emergency alert recipients', function () {
    $midwife = HealthcareWorker::factory()->create(['role' => 'bidan', 'status' => 'verified', 'region_code' => 'A']);
    $availableKader = HealthcareWorker::factory()->create(['role' => 'kader', 'status' => 'verified', 'region_code' => 'A', 'is_available' => true]);
    $unavailableKader = HealthcareWorker::factory()->create(['role' => 'kader', 'status' => 'verified', 'region_code' => 'A', 'is_available' => false]);
    KaderAreaAssignment::create(['kader_id' => $availableKader->id, 'region_code' => 'A', 'kader_priority' => 'primary']);
    KaderAreaAssignment::create(['kader_id' => $unavailableKader->id, 'region_code' => 'A', 'kader_priority' => 'primary']);

    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Availability', 'gestational_age_weeks_at_registration' => 30, 'region_code' => 'A',
    ]);
    MidwifeAssignment::create([
        'pregnancy_id' => $pregnancy->id, 'midwife_id' => $midwife->id,
        'assignment_method' => 'auto_zonasi', 'is_active' => true, 'started_at' => now(),
    ]);
    $riskAssessment = $pregnancy->riskAssessments()->create([
        'risk_level' => 'tinggi', 'triggered_rule_codes' => ['bleeding_heavy'],
        'recommendation_text' => 'Segera ke faskes.', 'assessed_at' => now(),
    ]);

    $alert = app(EmergencyAlertService::class)->triggerManual($pregnancy, $riskAssessment);

    expect($alert->recipients()->where('healthcare_worker_id', $availableKader->id)->exists())->toBeTrue()
        ->and($alert->recipients()->where('healthcare_worker_id', $unavailableKader->id)->exists())->toBeFalse()
        // Bidan pendamping tetap jadi penerima meski nanti nonaktif (§34.3.1) — di sini masih aktif, jadi termasuk.
        ->and($alert->recipients()->where('healthcare_worker_id', $midwife->id)->exists())->toBeTrue();
});

it('escalates faster when the assigned midwife is currently unavailable', function () {
    $midwife = HealthcareWorker::factory()->create(['role' => 'bidan', 'status' => 'verified', 'region_code' => 'A', 'is_available' => false]);
    $secondaryKader = HealthcareWorker::factory()->create(['role' => 'kader', 'status' => 'verified', 'region_code' => 'A']);
    KaderAreaAssignment::create(['kader_id' => $secondaryKader->id, 'region_code' => 'A', 'kader_priority' => 'secondary']);

    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Cepat', 'gestational_age_weeks_at_registration' => 30, 'region_code' => 'A',
    ]);
    MidwifeAssignment::create([
        'pregnancy_id' => $pregnancy->id, 'midwife_id' => $midwife->id,
        'assignment_method' => 'auto_zonasi', 'is_active' => true, 'started_at' => now(),
    ]);
    $riskAssessment = $pregnancy->riskAssessments()->create([
        'risk_level' => 'tinggi', 'triggered_rule_codes' => ['bleeding_heavy'],
        'recommendation_text' => 'Segera ke faskes.', 'assessed_at' => now(),
    ]);
    $alert = $pregnancy->emergencyAlerts()->create([
        'trigger_type' => 'manual_button', 'risk_assessment_id' => $riskAssessment->id,
        'status' => 'pending', 'triggered_at' => now()->subMinutes(4),
    ]);

    // Di bawah batas normal (10 menit) tapi di atas batas dipercepat (3 menit).
    $count = app(EmergencyAlertService::class)->escalateOverdue();

    expect($count)->toBe(1);
    expect($alert->fresh()->escalated_to_kader_at)->not->toBeNull();
});

it('reactivates workers automatically once their unavailable_until date has passed', function () {
    $worker = HealthcareWorker::factory()->create([
        'status' => 'verified', 'is_available' => false, 'unavailable_until' => now()->subDay(),
    ]);
    $stillAway = HealthcareWorker::factory()->create([
        'status' => 'verified', 'is_available' => false, 'unavailable_until' => now()->addDays(3),
    ]);

    $this->artisan(ReactivateExpiredAvailability::class)->assertSuccessful();

    expect($worker->fresh()->is_available)->toBeTrue()
        ->and($stillAway->fresh()->is_available)->toBeFalse();
});
