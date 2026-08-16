<?php

use App\Models\HealthcareWorker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

describe('Staff Authentication (Bidan & Kader)', function () {
    test('renders login page on web', function () {
        $response = $this->get(route('auth.staff.login.show'));
        $response->assertOk();
    });

    test('lets a verified worker login using phone number (08 format)', function () {
        $worker = HealthcareWorker::factory()->create([
            'phone_number' => '081234500001',
            'password_hash' => Hash::make('password123'),
            'status' => 'verified',
            'role' => 'bidan',
        ]);

        $response = $this->post(route('auth.staff.login'), [
            'identifier' => '081234500001',
            'password' => 'password123',
        ]);

        $targetRoute = \Illuminate\Support\Facades\Route::has('bidan.dashboard') ? route('bidan.dashboard') : route('auth.staff.pending');
        $response->assertRedirect($targetRoute);
        $this->assertAuthenticatedAs($worker, 'staff');
    });

    test('lets a verified worker login using phone number starting with 8 (+62 style)', function () {
        $worker = HealthcareWorker::factory()->create([
            'phone_number' => '081234500002',
            'password_hash' => Hash::make('password123'),
            'status' => 'verified',
            'role' => 'bidan',
        ]);

        $response = $this->post(route('auth.staff.login'), [
            'identifier' => '81234500002',
            'password' => 'password123',
        ]);

        $targetRoute = \Illuminate\Support\Facades\Route::has('bidan.dashboard') ? route('bidan.dashboard') : route('auth.staff.pending');
        $response->assertRedirect($targetRoute);
        $this->assertAuthenticatedAs($worker, 'staff');
    });

    test('lets a verified worker login using STR number', function () {
        $worker = HealthcareWorker::factory()->create([
            'phone_number' => '081234500003',
            'str_number' => 'STR-BDN-2026-999',
            'password_hash' => Hash::make('password123'),
            'status' => 'verified',
            'role' => 'bidan',
        ]);

        $response = $this->post(route('auth.staff.login'), [
            'identifier' => 'STR-BDN-2026-999',
            'password' => 'password123',
        ]);

        $targetRoute = \Illuminate\Support\Facades\Route::has('bidan.dashboard') ? route('bidan.dashboard') : route('auth.staff.pending');
        $response->assertRedirect($targetRoute);
        $this->assertAuthenticatedAs($worker, 'staff');
    });

    test('redirects pending worker to pending verification page', function () {
        $worker = HealthcareWorker::factory()->create([
            'phone_number' => '081234500004',
            'password_hash' => Hash::make('password123'),
            'status' => 'pending',
            'role' => 'bidan',
        ]);

        $response = $this->post(route('auth.staff.login'), [
            'identifier' => '081234500004',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('auth.staff.pending'));
        $this->assertAuthenticatedAs($worker, 'staff');
    });

    test('blocks rejected worker and returns informative error', function () {
        $worker = HealthcareWorker::factory()->create([
            'phone_number' => '081234500005',
            'password_hash' => Hash::make('password123'),
            'status' => 'rejected',
            'role' => 'bidan',
        ]);

        $response = $this->post(route('auth.staff.login'), [
            'identifier' => '081234500005',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest('staff');
    });

    test('throttles login attempts after 5 failures', function () {
        HealthcareWorker::factory()->create([
            'phone_number' => '081234500006',
            'password_hash' => Hash::make('correct-password'),
            'status' => 'verified',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('auth.staff.login'), [
                'identifier' => '081234500006',
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post(route('auth.staff.login'), [
            'identifier' => '081234500006',
            'password' => 'correct-password',
        ]);

        $response->assertSessionHasErrors('password');
    });

    test('registers a new bidan with normalized phone number and status pending', function () {
        $response = $this->post(route('auth.staff.register'), [
            'full_name' => 'Bdn. Siti Aminah, S.Tr.Keb',
            'phone_number' => '85730676143', // user typed without 0 (e.g. +62 input)
            'role' => 'bidan',
            'str_number' => 'STR-2026-112233',
            'password' => 'secure-pass-123',
            'password_confirmation' => 'secure-pass-123',
        ]);

        $response->assertRedirect(route('auth.staff.pending'));

        $this->assertDatabaseHas('healthcare_workers', [
            'phone_number' => '085730676143', // normalized to 08
            'role' => 'bidan',
            'str_number' => 'STR-2026-112233',
            'status' => 'pending',
        ]);

        $worker = HealthcareWorker::where('phone_number', '085730676143')->first();
        expect(password_verify('secure-pass-123', $worker->password_hash))->toBeTrue();
        $this->assertAuthenticatedAs($worker, 'staff');
    });

    test('registers a new kader with appointment letter ref', function () {
        $response = $this->post(route('auth.staff.register'), [
            'full_name' => 'Kader Nurul Hidayah',
            'phone_number' => '081299887766',
            'role' => 'kader',
            'appointment_letter_ref' => 'SK-DESA-0088-2026',
            'password' => 'secure-pass-123',
            'password_confirmation' => 'secure-pass-123',
        ]);

        $response->assertRedirect(route('auth.staff.pending'));

        $this->assertDatabaseHas('healthcare_workers', [
            'phone_number' => '081299887766',
            'role' => 'kader',
            'appointment_letter_ref' => 'SK-DESA-0088-2026',
            'status' => 'pending',
        ]);
    });

    test('lets an admin login using email from unified login page', function () {
        $admin = \App\Models\AdminUser::factory()->create([
            'email' => 'superadmin@sigadis.test',
            'password_hash' => Hash::make('password123'),
        ]);

        $response = $this->post(route('auth.staff.login'), [
            'identifier' => 'superadmin@sigadis.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
    });

    test('rejects pregnant user login on web with informative mobile app notice', function () {
        \App\Models\PregnantUser::factory()->create([
            'phone_number' => '081234500099',
            'password_hash' => Hash::make('password123'),
        ]);

        $response = $this->post(route('auth.staff.login'), [
            'identifier' => '081234500099',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('identifier');
        $this->assertGuest('staff');
        $this->assertGuest('pregnant');
    });

    test('rejects healthcare worker login on mobile app with informative web portal notice', function () {
        HealthcareWorker::factory()->create([
            'phone_number' => '081234500088',
            'password_hash' => Hash::make('password123'),
            'status' => 'verified',
            'role' => 'bidan',
        ]);

        $response = $this->post(route('mobile.login'), [
            'identifier' => '081234500088',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('identifier');
        $this->assertGuest('staff');
        $this->assertGuest('pregnant');
    });

    test('lets staff logout properly', function () {
        $worker = HealthcareWorker::factory()->create(['status' => 'verified']);

        $this->actingAs($worker, 'staff')
            ->post(route('auth.staff.logout'))
            ->assertRedirect(route('auth.staff.login.show'));

        $this->assertGuest('staff');
    });
});
