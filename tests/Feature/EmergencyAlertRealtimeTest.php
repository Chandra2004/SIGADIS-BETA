<?php

use App\Contracts\PushNotificationGateway;
use App\Events\EmergencyAlertBroadcast;
use App\Listeners\SendPushForEmergencyAlert;
use App\Models\DeviceToken;
use App\Models\HealthcareWorker;
use App\Models\KaderAreaAssignment;
use App\Models\MidwifeAssignment;
use App\Models\PregnantUser;
use App\Services\EmergencyAlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

function pregnancyWithRecipients(): array
{
    $midwife = HealthcareWorker::factory()->create(['role' => 'bidan', 'status' => 'verified', 'region_code' => 'A']);
    $kader = HealthcareWorker::factory()->create(['role' => 'kader', 'status' => 'verified', 'region_code' => 'A']);
    KaderAreaAssignment::create(['kader_id' => $kader->id, 'region_code' => 'A', 'kader_priority' => 'primary']);

    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Realtime', 'gestational_age_weeks_at_registration' => 30, 'region_code' => 'A',
    ]);
    MidwifeAssignment::create([
        'pregnancy_id' => $pregnancy->id, 'midwife_id' => $midwife->id,
        'assignment_method' => 'auto_zonasi', 'is_active' => true, 'started_at' => now(),
    ]);
    $riskAssessment = $pregnancy->riskAssessments()->create([
        'risk_level' => 'tinggi', 'triggered_rule_codes' => ['bleeding_heavy'],
        'recommendation_text' => 'Segera ke faskes.', 'assessed_at' => now(),
    ]);

    return [$pregnancy, $riskAssessment, $midwife, $kader];
}

it('broadcasts to every recipient when an emergency alert is triggered', function () {
    Event::fake([EmergencyAlertBroadcast::class]);
    [$pregnancy, $riskAssessment, $midwife, $kader] = pregnancyWithRecipients();

    app(EmergencyAlertService::class)->triggerManual($pregnancy, $riskAssessment);

    Event::assertDispatched(EmergencyAlertBroadcast::class, function ($event) use ($midwife, $kader) {
        return in_array($midwife->id, $event->recipientWorkerIds, true)
            && in_array($kader->id, $event->recipientWorkerIds, true);
    });
});

it('does not broadcast when there are no recipients', function () {
    Event::fake([EmergencyAlertBroadcast::class]);

    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Sendiri', 'gestational_age_weeks_at_registration' => 30, 'region_code' => 'Z',
    ]);
    $riskAssessment = $pregnancy->riskAssessments()->create([
        'risk_level' => 'tinggi', 'triggered_rule_codes' => ['bleeding_heavy'],
        'recommendation_text' => 'Segera ke faskes.', 'assessed_at' => now(),
    ]);

    app(EmergencyAlertService::class)->triggerManual($pregnancy, $riskAssessment);

    Event::assertNotDispatched(EmergencyAlertBroadcast::class);
});

it('sends a push notification to every active device token of the recipients', function () {
    [$pregnancy, $riskAssessment, $midwife, $kader] = pregnancyWithRecipients();
    DeviceToken::create([
        'healthcare_worker_id' => $midwife->id, 'fcm_token' => 'token-midwife',
        'platform' => 'mobile', 'registered_at' => now(), 'is_active' => true,
    ]);
    DeviceToken::create([
        'healthcare_worker_id' => $kader->id, 'fcm_token' => 'token-kader-inactive',
        'platform' => 'mobile', 'registered_at' => now(), 'is_active' => false,
    ]);

    $alert = $pregnancy->emergencyAlerts()->create([
        'trigger_type' => 'manual_button', 'risk_assessment_id' => $riskAssessment->id,
        'status' => 'pending', 'triggered_at' => now(),
    ]);
    $event = new EmergencyAlertBroadcast($alert, [$midwife->id, $kader->id]);

    $gateway = Mockery::mock(PushNotificationGateway::class);
    $gateway->shouldReceive('send')->once()->withArgs(function ($tokens) {
        return $tokens->contains('token-midwife') && ! $tokens->contains('token-kader-inactive');
    });

    (new SendPushForEmergencyAlert($gateway))->handle($event);
});

it('lets a staff member register and deactivate a device token', function () {
    $worker = HealthcareWorker::factory()->create(['status' => 'verified']);

    $this->actingAs($worker, 'staff')
        ->post(route('bidan.device-token.store'), ['fcm_token' => 'abc123', 'platform' => 'mobile'])
        ->assertRedirect();

    expect(DeviceToken::where('healthcare_worker_id', $worker->id)->where('is_active', true)->count())->toBe(1);

    $this->actingAs($worker, 'staff')
        ->delete(route('bidan.device-token.destroy'), ['fcm_token' => 'abc123'])
        ->assertRedirect();

    expect(DeviceToken::where('healthcare_worker_id', $worker->id)->where('is_active', true)->count())->toBe(0);
});
