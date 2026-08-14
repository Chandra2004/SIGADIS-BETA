<?php

use App\Models\AdminUser;
use App\Models\HealthcareWorker;
use App\Models\KaderAreaAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lets admin assign a verified kader to a region as primary or secondary', function () {
    $admin = AdminUser::factory()->create();
    $kader = HealthcareWorker::factory()->create(['role' => 'kader', 'status' => 'verified']);

    $this->actingAs($admin, 'admin')
        ->post(route('admin.area-assignments.store'), [
            'kader_id' => $kader->id, 'region_code' => 'X.01', 'kader_priority' => 'primary',
        ])
        ->assertRedirect();

    expect(KaderAreaAssignment::where('kader_id', $kader->id)->where('region_code', 'X.01')->exists())->toBeTrue();
});

it('rejects assigning a kader that is not verified', function () {
    $admin = AdminUser::factory()->create();
    $kader = HealthcareWorker::factory()->create(['role' => 'kader', 'status' => 'pending']);

    $this->actingAs($admin, 'admin')
        ->post(route('admin.area-assignments.store'), [
            'kader_id' => $kader->id, 'region_code' => 'X.01', 'kader_priority' => 'primary',
        ])
        ->assertSessionHasErrors('kader_id');
});

it('rejects assigning a bidan account as a kader area assignment', function () {
    $admin = AdminUser::factory()->create();
    $bidan = HealthcareWorker::factory()->create(['role' => 'bidan', 'status' => 'verified']);

    $this->actingAs($admin, 'admin')
        ->post(route('admin.area-assignments.store'), [
            'kader_id' => $bidan->id, 'region_code' => 'X.01', 'kader_priority' => 'primary',
        ])
        ->assertSessionHasErrors('kader_id');
});

it('blocks a duplicate assignment of the same kader to the same region', function () {
    $admin = AdminUser::factory()->create();
    $kader = HealthcareWorker::factory()->create(['role' => 'kader', 'status' => 'verified']);
    KaderAreaAssignment::create(['kader_id' => $kader->id, 'region_code' => 'X.01', 'kader_priority' => 'primary']);

    $this->actingAs($admin, 'admin')
        ->post(route('admin.area-assignments.store'), [
            'kader_id' => $kader->id, 'region_code' => 'X.01', 'kader_priority' => 'secondary',
        ])
        ->assertStatus(422);

    expect(KaderAreaAssignment::where('kader_id', $kader->id)->count())->toBe(1);
});

it('lets admin remove a kader area assignment', function () {
    $admin = AdminUser::factory()->create();
    $kader = HealthcareWorker::factory()->create(['role' => 'kader', 'status' => 'verified']);
    $assignment = KaderAreaAssignment::create(['kader_id' => $kader->id, 'region_code' => 'X.01', 'kader_priority' => 'primary']);

    $this->actingAs($admin, 'admin')
        ->delete(route('admin.area-assignments.destroy', $assignment))
        ->assertRedirect();

    expect(KaderAreaAssignment::find($assignment->id))->toBeNull();
});

it('shows region coverage counts on the admin dashboard', function () {
    $admin = AdminUser::factory()->create();
    HealthcareWorker::factory()->create(['role' => 'bidan', 'status' => 'verified', 'region_code' => 'X.01']);
    $kader = HealthcareWorker::factory()->create(['role' => 'kader', 'status' => 'verified', 'region_code' => 'X.01']);
    KaderAreaAssignment::create(['kader_id' => $kader->id, 'region_code' => 'X.01', 'kader_priority' => 'primary']);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.verifikasi.index'));

    $coverage = collect($response->viewData('page')['props']['coverage'])->firstWhere('region_code', 'X.01');
    expect($coverage)->not->toBeNull()
        ->and($coverage['bidan_verified'])->toBe(1)
        ->and($coverage['kader_primary'])->toBe(1)
        ->and($coverage['kader_secondary'])->toBe(0);
});
