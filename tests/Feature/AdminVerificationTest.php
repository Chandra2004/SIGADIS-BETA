<?php

use App\Models\AdminUser;
use App\Models\HealthcareWorker;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lets a bidan self-register with status pending and no dashboard access yet', function () {
    $response = $this->post(route('auth.staff.register'), [
        'full_name' => 'Bidan Baru',
        'phone_number' => '081299998888',
        'password' => 'password123',
        'role' => 'bidan',
        'str_number' => 'STR-9999',
        'region_code' => '33.08.05.2009',
    ]);

    $response->assertRedirect(route('auth.staff.pending'));

    $worker = HealthcareWorker::where('phone_number', '081299998888')->firstOrFail();
    expect($worker->status)->toBe('pending');

    if (\Illuminate\Support\Facades\Route::has('bidan.dashboard')) {
        $this->actingAs($worker, 'staff')
            ->get(route('bidan.dashboard'))
            ->assertRedirect(route('auth.staff.pending'));
    }
});

it('lets admin verify a pending worker, after which they can access the dashboard', function () {
    $admin = AdminUser::factory()->create();
    $worker = HealthcareWorker::factory()->create(['status' => 'pending']);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.verifikasi.index'))
        ->assertSuccessful();

    $this->actingAs($admin, 'admin')
        ->post(route('admin.verifikasi.verify', $worker))
        ->assertRedirect();

    $worker->refresh();
    expect($worker->status)->toBe('verified')
        ->and($worker->verified_by_admin_id)->toBe($admin->id);

    if (\Illuminate\Support\Facades\Route::has('bidan.dashboard')) {
        $this->actingAs($worker, 'staff')
            ->get(route('bidan.dashboard'))
            ->assertSuccessful();
    }
});

it('lets admin view the main admin dashboard with overview metrics and fast cards', function () {
    $admin = AdminUser::factory()->create();

    $this->actingAs($admin, 'admin')
        ->get(route('admin.dashboard'))
        ->assertSuccessful();
});

it('lets admin reject a pending worker with a required note', function () {
    $admin = AdminUser::factory()->create();
    $worker = HealthcareWorker::factory()->create(['status' => 'pending']);

    $this->actingAs($admin, 'admin')
        ->post(route('admin.verifikasi.reject', $worker), ['note' => 'STR tidak dapat diverifikasi.'])
        ->assertRedirect();

    expect($worker->fresh())->status->toBe('rejected')->admin_note->toBe('STR tidak dapat diverifikasi.');
});

it('requires a note when rejecting a worker', function () {
    $admin = AdminUser::factory()->create();
    $worker = HealthcareWorker::factory()->create(['status' => 'pending']);

    $this->actingAs($admin, 'admin')
        ->post(route('admin.verifikasi.reject', $worker), [])
        ->assertSessionHasErrors('note');

    expect($worker->fresh()->status)->toBe('pending');
});

it('lets admin cancel a rejection within 24 hours, returning the worker to pending', function () {
    $admin = AdminUser::factory()->create();
    $worker = HealthcareWorker::factory()->create([
        'status' => 'rejected', 'verified_by_admin_id' => $admin->id, 'verified_at' => now()->subHours(2),
    ]);

    $this->actingAs($admin, 'admin')
        ->post(route('admin.verifikasi.cancel-reject', $worker))
        ->assertRedirect();

    expect($worker->fresh())
        ->status->toBe('pending')
        ->verified_at->toBeNull();
});

it('blocks cancelling a rejection after the 24 hour window', function () {
    $admin = AdminUser::factory()->create();
    $worker = HealthcareWorker::factory()->create([
        'status' => 'rejected', 'verified_by_admin_id' => $admin->id, 'verified_at' => now()->subDays(2),
    ]);

    $this->actingAs($admin, 'admin')
        ->post(route('admin.verifikasi.cancel-reject', $worker))
        ->assertStatus(422);

    expect($worker->fresh()->status)->toBe('rejected');
});

it('blocks re-verifying or re-rejecting a worker that was already reviewed', function () {
    $admin = AdminUser::factory()->create();
    $worker = HealthcareWorker::factory()->create([
        'status' => 'verified', 'verified_by_admin_id' => $admin->id, 'verified_at' => now(),
    ]);

    $this->actingAs($admin, 'admin')->post(route('admin.verifikasi.reject', $worker), ['note' => 'Alasan penolakan.'])->assertStatus(422);
    $this->actingAs($admin, 'admin')->post(route('admin.verifikasi.verify', $worker))->assertStatus(422);

    expect($worker->fresh()->status)->toBe('verified');
});
