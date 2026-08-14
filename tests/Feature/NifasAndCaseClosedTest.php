<?php

use App\Models\HealthcareWorker;
use App\Models\PregnantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function assignedPregnancy(): array
{
    $midwife = HealthcareWorker::factory()->create(['status' => 'verified', 'region_code' => 'A']);
    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Nifas', 'gestational_age_weeks_at_registration' => 38, 'region_code' => 'A',
    ]);
    $pregnancy->midwifeAssignments()->create([
        'midwife_id' => $midwife->id, 'assignment_method' => 'auto_zonasi', 'is_active' => true, 'started_at' => now(),
    ]);

    return [$midwife, $pregnancy];
}

it('lets the assigned midwife mark a patient as delivered, transitioning to nifas', function () {
    [$midwife, $pregnancy] = assignedPregnancy();

    $this->actingAs($midwife, 'staff')
        ->post(route('bidan.patients.mark-delivered', $pregnancy->id), [
            'delivered_at' => now()->toDateString(),
            'delivery_notes' => 'Persalinan normal, bayi sehat.',
        ])
        ->assertRedirect();

    expect($pregnancy->fresh())
        ->status->toBe('nifas')
        ->nifas_started_at->not->toBeNull()
        ->delivery_notes->toBe('Persalinan normal, bayi sehat.');
});

it('lets the midwife cancel nifas status within 24 hours', function () {
    [$midwife, $pregnancy] = assignedPregnancy();
    $pregnancy->update(['status' => 'nifas', 'nifas_started_at' => now()->subHours(2), 'nifas_marked_at' => now()->subHours(2)]);

    $this->actingAs($midwife, 'staff')
        ->post(route('bidan.patients.cancel-nifas', $pregnancy->id))
        ->assertRedirect();

    expect($pregnancy->fresh())
        ->status->toBe('hamil')
        ->nifas_started_at->toBeNull()
        ->nifas_marked_at->toBeNull();
});

it('blocks cancelling nifas after the 24 hour window', function () {
    [$midwife, $pregnancy] = assignedPregnancy();
    $pregnancy->update(['status' => 'nifas', 'nifas_started_at' => now()->subDays(2), 'nifas_marked_at' => now()->subDays(2)]);

    $this->actingAs($midwife, 'staff')
        ->post(route('bidan.patients.cancel-nifas', $pregnancy->id))
        ->assertStatus(422);

    expect($pregnancy->fresh()->status)->toBe('nifas');
});

it('lets the midwife edit the delivery date within 24 hours without cancelling nifas', function () {
    [$midwife, $pregnancy] = assignedPregnancy();
    $pregnancy->update(['status' => 'nifas', 'nifas_started_at' => now()->subHours(2), 'nifas_marked_at' => now()->subHours(2)]);
    $corrected = now()->subDay()->toDateString();

    $this->actingAs($midwife, 'staff')
        ->post(route('bidan.patients.edit-delivery-date', $pregnancy->id), ['delivered_at' => $corrected])
        ->assertRedirect();

    $fresh = $pregnancy->fresh();
    expect($fresh->status)->toBe('nifas')
        ->and($fresh->nifas_started_at->toDateString())->toBe($corrected);
});

it('blocks editing the delivery date after the 24 hour window', function () {
    [$midwife, $pregnancy] = assignedPregnancy();
    $pregnancy->update(['status' => 'nifas', 'nifas_started_at' => now()->subDays(2), 'nifas_marked_at' => now()->subDays(2)]);

    $this->actingAs($midwife, 'staff')
        ->post(route('bidan.patients.edit-delivery-date', $pregnancy->id), ['delivered_at' => now()->toDateString()])
        ->assertStatus(422);
});

it('still allows cancelling within the window even when the delivery date itself was backdated', function () {
    [$midwife, $pregnancy] = assignedPregnancy();

    $this->actingAs($midwife, 'staff')
        ->post(route('bidan.patients.mark-delivered', $pregnancy->id), ['delivered_at' => now()->subDays(5)->toDateString()])
        ->assertRedirect();

    expect($pregnancy->fresh()->nifas_started_at->toDateString())->toBe(now()->subDays(5)->toDateString());

    $this->actingAs($midwife, 'staff')
        ->post(route('bidan.patients.cancel-nifas', $pregnancy->id))
        ->assertRedirect();

    expect($pregnancy->fresh()->status)->toBe('hamil');
});

it('lets the midwife close a case at any point, recording who closed it', function () {
    [$midwife, $pregnancy] = assignedPregnancy();
    $pregnancy->update(['status' => 'nifas', 'nifas_started_at' => now()->subDays(10)]);

    $this->actingAs($midwife, 'staff')
        ->post(route('bidan.patients.close-case', $pregnancy->id), [
            'confirmed' => true,
            'physical_recovery_status' => 'complete',
            'infant_growth_status' => 'on_target',
            'infant_weight_kg' => 4.5,
            'family_planning_status' => 'counseled_decided',
            'family_planning_method' => 'IUD',
            'next_steps' => 'Transition to Pediatric Care',
        ])
        ->assertRedirect(route('bidan.dashboard'));

    expect($pregnancy->fresh())
        ->status->toBe('case_closed')
        ->case_closed_at->not->toBeNull()
        ->case_closed_by->toBe($midwife->id);

    $assessment = $pregnancy->postpartumAssessment;
    expect($assessment->midwife_id)->toBe($midwife->id)
        ->and($assessment->physical_recovery_status)->toBe('complete')
        ->and((float) $assessment->infant_weight_kg)->toBe(4.5)
        ->and($assessment->family_planning_method)->toBe('IUD');
});

it('blocks closing a case without checking the postpartum confirmation checkbox', function () {
    [$midwife, $pregnancy] = assignedPregnancy();
    $pregnancy->update(['status' => 'nifas', 'nifas_started_at' => now()->subDays(10)]);

    $this->actingAs($midwife, 'staff')
        ->post(route('bidan.patients.close-case', $pregnancy->id), [
            'physical_recovery_status' => 'complete',
            'infant_growth_status' => 'on_target',
            'family_planning_status' => 'counseled_decided',
        ])
        ->assertSessionHasErrors('confirmed');

    expect($pregnancy->fresh()->status)->toBe('nifas');
});

it('forbids a kader from managing nifas or case-closed transitions', function () {
    [, $pregnancy] = assignedPregnancy();
    $kader = HealthcareWorker::factory()->create(['role' => 'kader', 'status' => 'verified']);
    $kader->kaderAreaAssignments()->create(['region_code' => 'A', 'kader_priority' => 'primary']);

    $this->actingAs($kader, 'staff')
        ->get(route('bidan.patients.show', $pregnancy->id))
        ->assertSuccessful();

    $this->actingAs($kader, 'staff')
        ->post(route('bidan.patients.mark-delivered', $pregnancy->id))
        ->assertForbidden();
});

it('forbids a midwife from viewing a patient not assigned to them', function () {
    [, $pregnancy] = assignedPregnancy();
    $otherMidwife = HealthcareWorker::factory()->create(['status' => 'verified', 'region_code' => 'B']);

    $this->actingAs($otherMidwife, 'staff')
        ->get(route('bidan.patients.show', $pregnancy->id))
        ->assertForbidden();
});
