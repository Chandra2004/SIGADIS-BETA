<?php

use App\Models\PregnantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('computes current gestational age and progress percent on the beranda page', function () {
    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Progres', 'gestational_age_weeks_at_registration' => 20, 'region_code' => 'X',
    ]);
    $pregnancy->forceFill(['created_at' => now()->subWeeks(2)])->save();

    $response = $this->actingAs($user, 'pregnant')->get(route('kehamilan.beranda'));

    $data = $response->viewData('page')['props']['pregnancy'];
    expect($data['current_gestational_age_weeks'])->toBe(22)
        ->and($data['progress_percent'])->toBe((int) round(22 / 40 * 100));
});

it('caps current gestational age at 42 weeks', function () {
    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Lewat HPL', 'gestational_age_weeks_at_registration' => 40, 'region_code' => 'X',
    ]);
    $pregnancy->forceFill(['created_at' => now()->subWeeks(10)])->save();

    $response = $this->actingAs($user, 'pregnant')->get(route('kehamilan.beranda'));

    expect($response->viewData('page')['props']['pregnancy']['current_gestational_age_weeks'])->toBe(42);
});
