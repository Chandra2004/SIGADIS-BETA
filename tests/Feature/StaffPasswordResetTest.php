<?php

use App\Models\HealthcareWorker;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

function staffOtpFor(string $phone): array
{
    $otp = app(OtpService::class);

    return [
        'code' => Cache::get($otp->codeKey($phone)),
        'request_id' => Cache::get($otp->requestId($phone)),
    ];
}

it('lets a worker reset their password via WhatsApp OTP end to end', function () {
    $worker = HealthcareWorker::factory()->create(['status' => 'verified', 'phone_number' => '081211112222']);

    $this->post(route('auth.staff.password-reset.send'), ['phone_number' => '081211112222'])
        ->assertRedirect(route('auth.staff.password-reset.verify.show', ['phone' => '081211112222']));

    $otp = staffOtpFor('081211112222');
    expect($otp['code'])->not->toBeNull();

    $this->post(route('auth.staff.password-reset.verify'), [
        'phone_number' => '081211112222',
        'otp_request_id' => $otp['request_id'],
        'otp_code' => $otp['code'],
    ])->assertRedirect(route('auth.staff.password-reset.form'));

    $this->post(route('auth.staff.password-reset.store'), [
        'password' => 'password-baru-123',
        'password_confirmation' => 'password-baru-123',
    ])->assertRedirect(route('auth.staff.login.show'))
      ->assertSessionHas('status');

    expect(password_verify('password-baru-123', $worker->fresh()->password_hash))->toBeTrue();

    // Verify worker can login with the new password
    $this->post(route('auth.staff.login'), [
        'identifier' => '081211112222',
        'password' => 'password-baru-123',
    ])->assertRedirect(route('bidan.dashboard'));
});

it('lets a pregnant user reset their password via WhatsApp OTP end to end', function () {
    $pregnantUser = \App\Models\PregnantUser::factory()->create(['phone_number' => '085712345678']);

    $this->post(route('auth.staff.password-reset.send'), ['phone_number' => '085712345678'])
        ->assertRedirect(route('auth.staff.password-reset.verify.show', ['phone' => '085712345678']));

    $otp = staffOtpFor('085712345678');
    expect($otp['code'])->not->toBeNull();

    $this->post(route('auth.staff.password-reset.verify'), [
        'phone_number' => '085712345678',
        'otp_request_id' => $otp['request_id'],
        'otp_code' => $otp['code'],
    ])->assertRedirect(route('auth.staff.password-reset.form'));

    $this->post(route('auth.staff.password-reset.store'), [
        'password' => 'password-ibu-baru-123',
        'password_confirmation' => 'password-ibu-baru-123',
    ])->assertRedirect(route('auth.staff.login.show'))
      ->assertSessionHas('status');

    expect(password_verify('password-ibu-baru-123', $pregnantUser->fresh()->password_hash))->toBeTrue();

    // Verify pregnant user can login with the new password
    $this->withHeaders(['X-Is-Native' => '1'])->post(route('auth.staff.login'), [
        'identifier' => '085712345678',
        'password' => 'password-ibu-baru-123',
    ])->assertRedirect(route('kehamilan.beranda'));
});

it('lets an admin user reset their password via Email OTP end to end', function () {
    $admin = \App\Models\AdminUser::factory()->create(['email' => 'admin.test@sigadis.test']);

    $this->post(route('auth.staff.password-reset.send'), ['phone_number' => 'admin.test@sigadis.test'])
        ->assertRedirect(route('auth.staff.password-reset.verify.show', ['phone' => 'admin.test@sigadis.test']));

    $otp = staffOtpFor('admin.test@sigadis.test');
    expect($otp['code'])->not->toBeNull();

    $this->post(route('auth.staff.password-reset.verify'), [
        'phone_number' => 'admin.test@sigadis.test',
        'otp_request_id' => $otp['request_id'],
        'otp_code' => $otp['code'],
    ])->assertRedirect(route('auth.staff.password-reset.form'));

    $this->post(route('auth.staff.password-reset.store'), [
        'password' => 'admin-password-baru',
        'password_confirmation' => 'admin-password-baru',
    ])->assertRedirect(route('auth.staff.login.show'))
      ->assertSessionHas('status');

    expect(password_verify('admin-password-baru', $admin->fresh()->password_hash))->toBeTrue();

    // Verify admin can login with the new password
    $this->post(route('auth.staff.login'), [
        'identifier' => 'admin.test@sigadis.test',
        'password' => 'admin-password-baru',
    ])->assertRedirect(route('admin.dashboard'));
});

it('does not reveal whether a phone number is registered when requesting a reset code', function () {
    $this->post(route('auth.staff.password-reset.send'), ['phone_number' => '089900001111'])
        ->assertRedirect(route('auth.staff.password-reset.verify.show', ['phone' => '089900001111']));
});

it('redirects to request form when accessing reset password form without a verified OTP session', function () {
    HealthcareWorker::factory()->create(['status' => 'verified', 'phone_number' => '081200002222']);

    $this->post(route('auth.staff.password-reset.store'), [
        'password' => 'password-baru-123',
        'password_confirmation' => 'password-baru-123',
    ])->assertRedirect(route('auth.staff.password-reset.request'));
});

it('rejects the wrong otp code', function () {
    HealthcareWorker::factory()->create(['status' => 'verified', 'phone_number' => '081200003333']);
    $this->post(route('auth.staff.password-reset.send'), ['phone_number' => '081200003333']);
    $otp = staffOtpFor('081200003333');

    $this->post(route('auth.staff.password-reset.verify'), [
        'phone_number' => '081200003333',
        'otp_request_id' => $otp['request_id'],
        'otp_code' => '000000',
    ])->assertSessionHasErrors('otp_code');
});
