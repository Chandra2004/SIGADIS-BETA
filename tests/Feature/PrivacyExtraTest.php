<?php

use App\Models\PregnantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function pregnancyWithConsentAndHistory(): array
{
    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Unduh', 'gestational_age_weeks_at_registration' => 20, 'region_code' => 'A',
    ]);
    $pregnancy->consents()->create(['consent_version' => '1.0', 'granted_at' => now()]);

    return [$user, $pregnancy];
}

it('toggles GPS permission', function () {
    [$user] = pregnancyWithConsentAndHistory();

    expect($user->fresh()->gps_permission_enabled)->toBeFalse();

    $this->actingAs($user, 'pregnant')
        ->post(route('kehamilan.privasi.gps-permission'), ['enabled' => true])
        ->assertRedirect();

    expect($user->fresh()->gps_permission_enabled)->toBeTrue();
});

it('toggles the share-data-with-midwife permission', function () {
    [$user] = pregnancyWithConsentAndHistory();

    expect($user->fresh()->share_data_with_midwife_enabled)->toBeTrue();

    $this->actingAs($user, 'pregnant')
        ->post(route('kehamilan.privasi.share-data-permission'), ['enabled' => false])
        ->assertRedirect();

    expect($user->fresh()->share_data_with_midwife_enabled)->toBeFalse();
});

it('downloads a PDF export of the pregnant user\'s own data', function () {
    [$user, $pregnancy] = pregnancyWithConsentAndHistory();
    $pregnancy->screeningSessions()->create(['session_type' => 'initial', 'started_at' => now(), 'is_complete' => true]);

    $response = $this->actingAs($user, 'pregnant')->get(route('kehamilan.privasi.export-data'));

    $response->assertSuccessful();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});
