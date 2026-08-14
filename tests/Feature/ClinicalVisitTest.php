<?php

use App\Models\HealthcareWorker;
use App\Models\MidwifeAssignment;
use App\Models\PregnantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function pregnancyWithAssignedMidwife(): array
{
    $midwife = HealthcareWorker::factory()->create(['role' => 'bidan', 'status' => 'verified', 'region_code' => 'A']);
    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Klinis', 'gestational_age_weeks_at_registration' => 30, 'region_code' => 'A',
    ]);
    MidwifeAssignment::create([
        'pregnancy_id' => $pregnancy->id, 'midwife_id' => $midwife->id,
        'assignment_method' => 'auto_zonasi', 'is_active' => true, 'started_at' => now(),
    ]);

    return [$midwife, $pregnancy];
}

it('lets the assigned midwife log a clinical visit with vitals and symptoms', function () {
    [$midwife, $pregnancy] = pregnancyWithAssignedMidwife();

    $this->actingAs($midwife, 'staff')
        ->post(route('bidan.patients.clinical-visits.store', $pregnancy), [
            'visit_type' => 'follow_up',
            'status_tag' => 'monitor',
            'blood_pressure_systolic' => 140,
            'blood_pressure_diastolic' => 90,
            'symptoms' => ['Trace Proteinuria'],
            'clinical_notes' => 'Advised bed rest.',
        ])
        ->assertRedirect();

    $visit = $pregnancy->clinicalVisits()->first();
    expect($visit->midwife_id)->toBe($midwife->id)
        ->and($visit->blood_pressure_systolic)->toBe(140)
        ->and($visit->symptoms)->toBe(['Trace Proteinuria']);
});

it('forbids a kader from logging a clinical visit', function () {
    [, $pregnancy] = pregnancyWithAssignedMidwife();
    $kader = HealthcareWorker::factory()->create(['role' => 'kader', 'status' => 'verified', 'region_code' => 'A']);
    $kader->kaderAreaAssignments()->create(['region_code' => 'A', 'kader_priority' => 'primary']);

    $this->actingAs($kader, 'staff')
        ->post(route('bidan.patients.clinical-visits.store', $pregnancy), [
            'visit_type' => 'routine_screening',
            'status_tag' => 'normal',
        ])
        ->assertForbidden();
});

it('shows clinical visits on the patient detail page', function () {
    [$midwife, $pregnancy] = pregnancyWithAssignedMidwife();
    $pregnancy->clinicalVisits()->create([
        'midwife_id' => $midwife->id, 'visit_type' => 'routine_screening', 'status_tag' => 'elevated',
        'blood_pressure_systolic' => 160, 'blood_pressure_diastolic' => 105,
        'symptoms' => ['Blurred Vision'], 'clinical_notes' => 'Admitted for observation.', 'visited_at' => now(),
    ]);

    $response = $this->actingAs($midwife, 'staff')->get(route('bidan.patients.show', $pregnancy));
    $visits = $response->viewData('page')['props']['clinicalVisits'];

    expect($visits)->toHaveCount(1)
        ->and($visits[0]['status_tag'])->toBe('elevated');
});

it('downloads a PDF export of the patient history', function () {
    [$midwife, $pregnancy] = pregnancyWithAssignedMidwife();

    $response = $this->actingAs($midwife, 'staff')->get(route('bidan.patients.export-history', $pregnancy));

    $response->assertSuccessful();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('forbids exporting history for a patient outside the worker\'s assignment', function () {
    [, $pregnancy] = pregnancyWithAssignedMidwife();
    $otherMidwife = HealthcareWorker::factory()->create(['status' => 'verified', 'region_code' => 'Z']);

    $this->actingAs($otherMidwife, 'staff')
        ->get(route('bidan.patients.export-history', $pregnancy))
        ->assertForbidden();
});
