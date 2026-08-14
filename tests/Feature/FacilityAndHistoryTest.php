<?php

use App\Models\Facility;
use App\Models\PregnantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows facilities sorted with the pregnant user\'s own region first', function () {
    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Faskes', 'gestational_age_weeks_at_registration' => 10, 'region_code' => 'A',
    ]);

    Facility::create(['name' => 'Faskes Jauh', 'type' => 'klinik', 'region_code' => 'B', 'address' => '-']);
    Facility::create(['name' => 'Faskes Dekat', 'type' => 'puskesmas', 'region_code' => 'A', 'address' => '-']);

    $response = $this->actingAs($user, 'pregnant')->get(route('kehamilan.faskes'));

    $response->assertSuccessful();
    $facilities = $response->viewData('page')['props']['facilities'];
    expect($facilities[0]['name'])->toBe('Faskes Dekat');
});

it('shows the pregnant user\'s own screening and referral history', function () {
    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Riwayat', 'gestational_age_weeks_at_registration' => 10, 'region_code' => 'A',
    ]);
    $pregnancy->screeningSessions()->create(['session_type' => 'initial', 'started_at' => now(), 'is_complete' => true]);

    $response = $this->actingAs($user, 'pregnant')->get(route('kehamilan.riwayat'));

    $response->assertSuccessful();
    expect($response->viewData('page')['props']['screeningSessions'])->toHaveCount(1);
});
