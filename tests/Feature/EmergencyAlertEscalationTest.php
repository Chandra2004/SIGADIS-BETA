<?php

use App\Events\EmergencyAlertBroadcast;
use App\Models\HealthcareWorker;
use App\Models\KaderAreaAssignment;
use App\Models\MidwifeAssignment;
use App\Models\PregnantUser;
use App\Services\EmergencyAlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

function overdueAlert(): array
{
    $midwife = HealthcareWorker::factory()->create(['role' => 'bidan', 'status' => 'verified', 'region_code' => 'A']);
    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Eskalasi', 'gestational_age_weeks_at_registration' => 30, 'region_code' => 'A',
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
        'status' => 'pending', 'triggered_at' => now()->subMinutes(15),
    ]);

    return [$pregnancy, $alert];
}

it('escalates an overdue alert to secondary kader and broadcasts to them', function () {
    Event::fake([EmergencyAlertBroadcast::class]);
    [$pregnancy, $alert] = overdueAlert();
    $secondaryKader = HealthcareWorker::factory()->create(['role' => 'kader', 'status' => 'verified', 'region_code' => 'A']);
    KaderAreaAssignment::create(['kader_id' => $secondaryKader->id, 'region_code' => 'A', 'kader_priority' => 'secondary']);

    $count = app(EmergencyAlertService::class)->escalateOverdue();

    expect($count)->toBe(1);
    expect($alert->fresh()->escalated_to_kader_at)->not->toBeNull();
    expect($alert->fresh()->recipients()->where('recipient_role_at_time', 'kader_secondary')->where('healthcare_worker_id', $secondaryKader->id)->exists())->toBeTrue();

    Event::assertDispatched(EmergencyAlertBroadcast::class, fn ($event) => in_array($secondaryKader->id, $event->recipientWorkerIds, true));
});

it('does not escalate an alert still within the timeout window', function () {
    [$pregnancy, $alert] = overdueAlert();
    $alert->update(['triggered_at' => now()->subMinutes(2)]);
    HealthcareWorker::factory()->create(['role' => 'kader', 'status' => 'verified', 'region_code' => 'A'])
        ->kaderAreaAssignments()->create(['region_code' => 'A', 'kader_priority' => 'secondary']);

    $count = app(EmergencyAlertService::class)->escalateOverdue();

    expect($count)->toBe(0);
    expect($alert->fresh()->escalated_to_kader_at)->toBeNull();
});

it('does not escalate an alert already being handled', function () {
    [$pregnancy, $alert] = overdueAlert();
    $alert->update(['status' => 'being_handled']);

    $count = app(EmergencyAlertService::class)->escalateOverdue();

    expect($count)->toBe(0);
});

it('does not re-escalate an alert that was already escalated', function () {
    [$pregnancy, $alert] = overdueAlert();
    $alert->update(['escalated_to_kader_at' => now()->subMinutes(5)]);

    $count = app(EmergencyAlertService::class)->escalateOverdue();

    expect($count)->toBe(0);
});

it('marks the alert as escalated even when no secondary kader is available, without broadcasting', function () {
    Event::fake([EmergencyAlertBroadcast::class]);
    [$pregnancy, $alert] = overdueAlert();

    $count = app(EmergencyAlertService::class)->escalateOverdue();

    expect($count)->toBe(1);
    expect($alert->fresh()->escalated_to_kader_at)->not->toBeNull();
    Event::assertNotDispatched(EmergencyAlertBroadcast::class);
});

it('runs the alerts:escalate command', function () {
    overdueAlert();

    $this->artisan('alerts:escalate')->assertSuccessful();
});

it('logs a warning and still sends the alert to the midwife when no kader primary is registered in the region', function () {
    Illuminate\Support\Facades\Log::spy();

    $midwife = HealthcareWorker::factory()->create(['role' => 'bidan', 'status' => 'verified', 'region_code' => 'B']);
    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Tanpa Kader', 'gestational_age_weeks_at_registration' => 30, 'region_code' => 'B',
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

    expect($alert->recipients()->count())->toBe(1)
        ->and($alert->recipients()->first()->healthcare_worker_id)->toBe($midwife->id);
    Illuminate\Support\Facades\Log::shouldHaveReceived('warning')->once();
});
