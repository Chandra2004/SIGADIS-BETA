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
    ])->assertRedirect(route('bidan.dashboard'));

    expect(password_verify('password-baru-123', $worker->fresh()->password_hash))->toBeTrue();
    $this->assertAuthenticatedAs($worker->fresh(), 'staff');
});

it('does not reveal whether a phone number is registered when requesting a reset code', function () {
    $this->post(route('auth.staff.password-reset.send'), ['phone_number' => '089900001111'])
        ->assertRedirect(route('auth.staff.password-reset.verify.show', ['phone' => '089900001111']));
});

it('blocks setting a new password without a verified OTP session', function () {
    HealthcareWorker::factory()->create(['status' => 'verified', 'phone_number' => '081200002222']);

    $this->post(route('auth.staff.password-reset.store'), [
        'password' => 'password-baru-123',
        'password_confirmation' => 'password-baru-123',
    ])->assertForbidden();
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
