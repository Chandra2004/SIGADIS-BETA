<?php

use App\Models\HealthcareWorker;
use App\Models\PregnantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('switches the active pregnancy profile under one phone number', function () {
    $user = PregnantUser::factory()->create();
    $first = $user->pregnancies()->create([
        'mother_name' => 'Anak Pertama', 'gestational_age_weeks_at_registration' => 10, 'region_code' => 'A',
    ]);
    $second = $user->pregnancies()->create([
        'mother_name' => 'Anak Kedua', 'gestational_age_weeks_at_registration' => 5, 'region_code' => 'A',
    ]);

    $this->actingAs($user, 'pregnant')->get(route('kehamilan.beranda'))
        ->assertSuccessful();

    $this->actingAs($user, 'pregnant')->post(route('kehamilan.switch-active', $second->id))
        ->assertRedirect(route('kehamilan.beranda'));

    $response = $this->actingAs($user, 'pregnant')->get(route('kehamilan.beranda'));
    expect($response->viewData('page')['props']['pregnancy']['id'])->toBe($second->id);
});

it('forbids switching to a pregnancy profile owned by another user', function () {
    $user = PregnantUser::factory()->create();
    $other = PregnantUser::factory()->create();
    $foreign = $other->pregnancies()->create([
        'mother_name' => 'Bukan Punya', 'gestational_age_weeks_at_registration' => 10, 'region_code' => 'A',
    ]);

    $this->actingAs($user, 'pregnant')->post(route('kehamilan.switch-active', $foreign->id))
        ->assertForbidden();
});

it('changes the assigned midwife and deactivates the previous assignment', function () {
    $oldMidwife = HealthcareWorker::factory()->create(['status' => 'verified', 'region_code' => 'A']);
    $newMidwife = HealthcareWorker::factory()->create(['status' => 'verified', 'region_code' => 'A']);

    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Ganti Bidan', 'gestational_age_weeks_at_registration' => 10, 'region_code' => 'A',
    ]);
    $oldAssignment = $pregnancy->midwifeAssignments()->create([
        'midwife_id' => $oldMidwife->id, 'assignment_method' => 'auto_zonasi', 'is_active' => true, 'started_at' => now(),
    ]);

    $this->actingAs($user, 'pregnant')
        ->post(route('kehamilan.ganti-bidan.store'), ['midwife_id' => $newMidwife->id])
        ->assertRedirect(route('kehamilan.beranda'));

    expect($oldAssignment->fresh())
        ->is_active->toBeFalse()
        ->ended_at->not->toBeNull();

    $active = $pregnancy->fresh()->activeMidwifeAssignment;
    expect($active->midwife_id)->toBe($newMidwife->id)
        ->and($active->assignment_method)->toBe('manual_pilih');
});
