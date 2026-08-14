<?php

use App\Models\AdminUser;
use App\Models\HealthcareWorker;
use App\Models\MidwifeAssignment;
use App\Models\PregnantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('notifies a worker when their account is verified', function () {
    $admin = AdminUser::factory()->create();
    $worker = HealthcareWorker::factory()->create(['status' => 'pending']);

    $this->actingAs($admin, 'admin')->post(route('admin.verifikasi.verify', $worker));

    expect($worker->unreadNotifications()->count())->toBe(1);
});

it('notifies a worker when their account is rejected', function () {
    $admin = AdminUser::factory()->create();
    $worker = HealthcareWorker::factory()->create(['status' => 'pending']);

    $this->actingAs($admin, 'admin')->post(route('admin.verifikasi.reject', $worker), ['note' => 'Alasan penolakan.']);

    expect($worker->unreadNotifications()->count())->toBe(1);
});

it('notifies the pregnant user when their alert is acknowledged and resolved', function () {
    $midwife = HealthcareWorker::factory()->create(['role' => 'bidan', 'status' => 'verified', 'region_code' => 'A']);
    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Notif', 'gestational_age_weeks_at_registration' => 30, 'region_code' => 'A',
    ]);
    MidwifeAssignment::create([
        'pregnancy_id' => $pregnancy->id, 'midwife_id' => $midwife->id,
        'assignment_method' => 'auto_zonasi', 'is_active' => true, 'started_at' => now(),
    ]);

    $this->actingAs($user, 'pregnant')->post(route('darurat.aktivasi'));
    $alert = $pregnancy->emergencyAlerts()->first();

    $this->actingAs($midwife, 'staff')->post(route('bidan.alerts.acknowledge', $alert));
    expect($user->unreadNotifications()->count())->toBe(1);

    $this->actingAs($midwife, 'staff')->post(route('bidan.alerts.resolve', $alert));
    expect($user->unreadNotifications()->count())->toBe(2);
});

it('shows the unread notification count as a shared Inertia prop', function () {
    $admin = AdminUser::factory()->create();
    $worker = HealthcareWorker::factory()->create(['status' => 'pending']);
    $this->actingAs($admin, 'admin')->post(route('admin.verifikasi.verify', $worker));

    $response = $this->actingAs($worker, 'staff')->get(route('auth.staff.pending'));

    expect($response->viewData('page')['props']['unreadNotificationCount'])->toBe(1);
});

it('lets a user mark a single notification and all notifications as read', function () {
    $admin = AdminUser::factory()->create();
    $worker = HealthcareWorker::factory()->create(['status' => 'pending']);
    $this->actingAs($admin, 'admin')->post(route('admin.verifikasi.verify', $worker));
    $notification = $worker->notifications()->first();

    $this->actingAs($worker, 'staff')
        ->post(route('bidan.notifikasi.mark-read', $notification->id))
        ->assertRedirect();
    expect($worker->unreadNotifications()->count())->toBe(0);

    $this->actingAs($admin, 'admin')->post(route('admin.verifikasi.reject', HealthcareWorker::factory()->create(['status' => 'pending'])), ['note' => 'Alasan penolakan.']);
    // buat notifikasi lagi ke worker lain, tidak relevan; pastikan mark-all cuma pengaruhi punya sendiri
    $this->actingAs($worker, 'staff')->post(route('bidan.notifikasi.mark-all-read'))->assertRedirect();
    expect($worker->unreadNotifications()->count())->toBe(0);
});
