<?php

use App\Models\PregnantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects to the nifas transition screen once before showing beranda', function () {
    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Nifas', 'gestational_age_weeks_at_registration' => 40, 'region_code' => 'A',
        'status' => 'nifas', 'nifas_started_at' => now(), 'nifas_marked_at' => now(),
    ]);

    $this->actingAs($user, 'pregnant')
        ->get(route('kehamilan.beranda'))
        ->assertRedirect(route('kehamilan.nifas.transisi'));

    $this->actingAs($user, 'pregnant')
        ->get(route('kehamilan.nifas.transisi'))
        ->assertSuccessful();

    $this->actingAs($user, 'pregnant')
        ->post(route('kehamilan.nifas.transisi.ack'))
        ->assertRedirect(route('kehamilan.beranda'));

    $this->actingAs($user, 'pregnant')
        ->get(route('kehamilan.beranda'))
        ->assertSuccessful();
});

it('does not show the transition screen to a pregnancy that is not in nifas', function () {
    $user = PregnantUser::factory()->create();
    $user->pregnancies()->create([
        'mother_name' => 'Ibu Hamil', 'gestational_age_weeks_at_registration' => 20, 'region_code' => 'A',
    ]);

    $this->actingAs($user, 'pregnant')->get(route('kehamilan.beranda'))->assertSuccessful();
});
