<?php

use App\Models\AdminUser;
use App\Models\HealthcareWorker;
use App\Models\MidwifeAssignment;
use App\Models\PregnantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function alertWithRecipient(int $triggeredMinutesAgo, ?int $acknowledgedAfterSeconds): array
{
    $midwife = HealthcareWorker::factory()->create(['role' => 'bidan', 'status' => 'verified', 'region_code' => 'A']);
    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Metrik', 'gestational_age_weeks_at_registration' => 30, 'region_code' => 'A',
    ]);
    MidwifeAssignment::create([
        'pregnancy_id' => $pregnancy->id, 'midwife_id' => $midwife->id,
        'assignment_method' => 'auto_zonasi', 'is_active' => true, 'started_at' => now(),
    ]);
    $riskAssessment = $pregnancy->riskAssessments()->create([
        'risk_level' => 'tinggi', 'triggered_rule_codes' => [], 'recommendation_text' => '-', 'assessed_at' => now(),
    ]);
    $triggeredAt = now()->subMinutes($triggeredMinutesAgo);
    $alert = $pregnancy->emergencyAlerts()->create([
        'trigger_type' => 'manual_button', 'risk_assessment_id' => $riskAssessment->id,
        'status' => 'pending', 'triggered_at' => $triggeredAt,
    ]);
    $alert->recipients()->create([
        'healthcare_worker_id' => $midwife->id, 'recipient_role_at_time' => 'bidan_utama', 'delivery_status' => 'sent',
        'acknowledged_at' => $acknowledgedAfterSeconds !== null ? $triggeredAt->clone()->addSeconds($acknowledgedAfterSeconds) : null,
    ]);

    return [$midwife, $alert];
}

it('sets acknowledged_at on the alert recipient row when a worker acknowledges', function () {
    [$midwife, $alert] = alertWithRecipient(5, null);

    $this->actingAs($midwife, 'staff')->post(route('bidan.alerts.acknowledge', $alert));

    $recipient = $alert->recipients()->where('healthcare_worker_id', $midwife->id)->first();
    expect($recipient->acknowledged_at)->not->toBeNull();
});

it('computes average and median response time from acknowledged alerts', function () {
    alertWithRecipient(10, 120); // 2 menit
    alertWithRecipient(10, 360); // 6 menit
    alertWithRecipient(10, null); // belum direspon

    $admin = AdminUser::factory()->create();
    $response = $this->actingAs($admin, 'admin')->get(route('admin.reporting.index'));

    $summary = $response->viewData('page')['props']['summary'];
    expect($summary['total_alerts'])->toBe(3)
        ->and($summary['responded_count'])->toBe(2)
        ->and($summary['unresponded_count'])->toBe(1)
        ->and($summary['average_seconds'])->toBe(240)
        ->and($summary['median_seconds'])->toBe(240);
});

it('excludes alerts outside the selected date range', function () {
    [, $oldAlert] = alertWithRecipient(10, 60);
    $oldAlert->update(['triggered_at' => now()->subDays(60)]);
    $oldAlert->recipients()->update(['acknowledged_at' => now()->subDays(60)->addSeconds(60)]);

    $admin = AdminUser::factory()->create();
    $response = $this->actingAs($admin, 'admin')->get(route('admin.reporting.index', [
        'from' => now()->subDays(7)->toDateString(),
        'to' => now()->toDateString(),
    ]));

    expect($response->viewData('page')['props']['summary']['total_alerts'])->toBe(0);
});

it('exports response time data as csv', function () {
    alertWithRecipient(10, 90);

    $admin = AdminUser::factory()->create();
    $response = $this->actingAs($admin, 'admin')->get(route('admin.reporting.export'));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($response->streamedContent())->toContain('alert_id')->toContain('waktu_respons_detik');
});

it('blocks a non-admin guest from viewing the reporting page', function () {
    $this->get(route('admin.reporting.index'))->assertRedirect(route('auth.admin.login.show'));
});
