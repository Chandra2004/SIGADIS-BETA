<?php

use App\Models\PregnantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the profil hub page with account info', function () {
    $user = PregnantUser::factory()->create(['full_name' => 'Ibu Profil', 'phone_number' => '081234567890']);
    $user->pregnancies()->create(['mother_name' => 'Ibu Profil', 'gestational_age_weeks_at_registration' => 20, 'region_code' => 'A']);

    $response = $this->actingAs($user, 'pregnant')->get(route('kehamilan.profil'));

    $response->assertSuccessful();
    $props = $response->viewData('page')['props'];
    expect($props['motherName'])->toBe('Ibu Profil')
        ->and($props['phoneNumber'])->toBe('081234567890')
        ->and($props['hasActivePregnancy'])->toBeTrue()
        ->and($props['canChangeMidwife'])->toBeTrue();
});

it('marks canChangeMidwife false when the case is closed', function () {
    $user = PregnantUser::factory()->create();
    $user->pregnancies()->create([
        'mother_name' => 'Ibu Tutup', 'gestational_age_weeks_at_registration' => 20, 'region_code' => 'A',
        'status' => 'case_closed', 'case_closed_at' => now(),
    ]);

    $response = $this->actingAs($user, 'pregnant')->get(route('kehamilan.profil'));

    expect($response->viewData('page')['props']['canChangeMidwife'])->toBeFalse();
});

it('marks hasActivePregnancy false for a user with no pregnancy profile yet', function () {
    $user = PregnantUser::factory()->create();

    $response = $this->actingAs($user, 'pregnant')->get(route('kehamilan.profil'));

    expect($response->viewData('page')['props']['hasActivePregnancy'])->toBeFalse();
});
