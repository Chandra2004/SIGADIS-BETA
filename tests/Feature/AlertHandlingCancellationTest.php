<?php

use App\Models\AlertHandlingCancellation;
use App\Models\Facility;
use App\Models\HealthcareWorker;
use App\Models\MidwifeAssignment;
use App\Models\PregnantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function beingHandledAlert(): array
{
    $midwife = HealthcareWorker::factory()->create(['status' => 'verified', 'region_code' => 'A']);
    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Alert', 'gestational_age_weeks_at_registration' => 30, 'region_code' => 'A',
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
        'status' => 'being_handled', 'triggered_at' => now(),
        'handled_by_id' => $midwife->id, 'handled_at' => now(),
    ]);

    return [$midwife, $alert];
}

it('lets the handler cancel a mis-tap within the 2 minute window, returning the alert to the queue', function () {
    [$midwife, $alert] = beingHandledAlert();

    $this->actingAs($midwife, 'staff')
        ->post(route('bidan.alerts.cancel-handling', $alert->id))
        ->assertRedirect(route('bidan.dashboard'));

    $fresh = $alert->fresh();
    expect($fresh->status)->toBe('delivered')
        ->and($fresh->handled_by_id)->toBeNull()
        ->and($fresh->handled_at)->toBeNull();

    $log = AlertHandlingCancellation::first();
    expect($log->cancelled_handler_id)->toBe($midwife->id)
        ->and($log->emergency_alert_id)->toBe($alert->id);
});

it('blocks cancelling handling after the 2 minute window', function () {
    [$midwife, $alert] = beingHandledAlert();
    $alert->update(['handled_at' => now()->subMinutes(5)]);

    $this->actingAs($midwife, 'staff')
        ->post(route('bidan.alerts.cancel-handling', $alert->id))
        ->assertStatus(422);

    expect($alert->fresh()->status)->toBe('being_handled');
});

it('blocks cancelling handling once a referral has been made', function () {
    [$midwife, $alert] = beingHandledAlert();
    $facility = Facility::create(['name' => 'RS A', 'type' => 'rumah_sakit', 'region_code' => 'A', 'address' => '-']);
    $alert->referrals()->create([
        'pregnancy_id' => $alert->pregnancy_id, 'facility_id' => $facility->id,
        'referred_by_id' => $midwife->id, 'referred_at' => now(),
    ]);

    $this->actingAs($midwife, 'staff')
        ->post(route('bidan.alerts.cancel-handling', $alert->id))
        ->assertStatus(422);
});

it('blocks a different worker from cancelling someone else\'s handling', function () {
    [, $alert] = beingHandledAlert();
    // Kader wilayah sama: lolos scoping (patientsFor), tapi bukan handler alert ini -> 422 dari canCancelHandling.
    $sameRegionKader = HealthcareWorker::factory()->create(['role' => 'kader', 'status' => 'verified', 'region_code' => 'A']);
    $sameRegionKader->kaderAreaAssignments()->create(['region_code' => 'A', 'kader_priority' => 'primary']);

    $this->actingAs($sameRegionKader, 'staff')
        ->post(route('bidan.alerts.cancel-handling', $alert->id))
        ->assertStatus(422);
});

it('exposes the cancel-handling expiry timestamp on the alert detail page', function () {
    [$midwife, $alert] = beingHandledAlert();

    $response = $this->actingAs($midwife, 'staff')->get(route('bidan.alerts.show', $alert));

    $expiresAt = $response->viewData('page')['props']['alert']['cancel_handling_expires_at'];
    expect($expiresAt)->not->toBeNull();
    expect((int) \Carbon\Carbon::parse($expiresAt)->diffInSeconds($alert->handled_at->addMinutes(2), false))->toBe(0);
});

it('forbids a worker with no assignment to the patient from cancelling handling at all', function () {
    [, $alert] = beingHandledAlert();
    $unrelatedWorker = HealthcareWorker::factory()->create(['status' => 'verified', 'region_code' => 'Z']);

    $this->actingAs($unrelatedWorker, 'staff')
        ->post(route('bidan.alerts.cancel-handling', $alert->id))
        ->assertForbidden();
});
