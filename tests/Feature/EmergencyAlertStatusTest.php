<?php

use App\Models\HealthcareWorker;
use App\Models\MidwifeAssignment;
use App\Models\PregnantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the emergency status timeline after activating SOS', function () {
    $midwife = HealthcareWorker::factory()->create(['role' => 'bidan', 'status' => 'verified', 'region_code' => 'A']);
    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Status', 'gestational_age_weeks_at_registration' => 30, 'region_code' => 'A',
    ]);
    MidwifeAssignment::create([
        'pregnancy_id' => $pregnancy->id, 'midwife_id' => $midwife->id,
        'assignment_method' => 'auto_zonasi', 'is_active' => true, 'started_at' => now(),
    ]);

    $this->actingAs($user, 'pregnant')->post(route('darurat.aktivasi'));

    $response = $this->actingAs($user, 'pregnant')->get(route('darurat.status'));
    $response->assertSuccessful();
    $steps = $response->viewData('page')['props']['alert']['steps'];

    expect($response->viewData('page')['props']['midwifePhone'])->toBe($midwife->phone_number);
    expect($steps[0]['key'])->toBe('signal_sent')->and($steps[0]['done'])->toBeTrue()
        ->and($steps[1]['done'])->toBeFalse()
        ->and($steps[2]['done'])->toBeFalse();

    $alert = $pregnancy->emergencyAlerts()->first();
    $this->actingAs($midwife, 'staff')->post(route('bidan.alerts.acknowledge', $alert));

    $steps = $this->actingAs($user, 'pregnant')->get(route('darurat.status'))
        ->viewData('page')['props']['alert']['steps'];
    expect($steps[1]['done'])->toBeTrue()
        ->and($steps[1]['detail'])->toBe($midwife->full_name)
        ->and($steps[2]['done'])->toBeFalse();

    $this->actingAs($midwife, 'staff')->post(route('bidan.alerts.resolve', $alert));

    $steps = $this->actingAs($user, 'pregnant')->get(route('darurat.status'))
        ->viewData('page')['props']['alert']['steps'];
    expect($steps[2]['done'])->toBeTrue();
});

it('redirects to beranda when there is no open emergency alert', function () {
    $user = PregnantUser::factory()->create();
    $user->pregnancies()->create([
        'mother_name' => 'Ibu Tenang', 'gestational_age_weeks_at_registration' => 30, 'region_code' => 'A',
    ]);

    $this->actingAs($user, 'pregnant')->get(route('darurat.status'))->assertRedirect(route('kehamilan.beranda'));
});
