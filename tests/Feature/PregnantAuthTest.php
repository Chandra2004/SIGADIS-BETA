<?php

use App\Models\PregnantUser;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function pregnantOtpFor(string $phone): array
{
    $otp = app(OtpService::class);

    return [
        'code' => Cache::get($otp->codeKey($phone)),
        'request_id' => Cache::get($otp->requestId($phone)),
    ];
}

describe('Pregnant User Authentication (OTP WhatsApp)', function () {
    test('sends OTP when phone number is entered without 0 (e.g. 85730676143 from +62 UI)', function () {
        $response = $this->post(route('auth.pregnant.otp.send'), [
            'phone_number' => '85730676143',
        ]);

        $response->assertRedirect(route('auth.pregnant.verify.show', ['phone' => '085730676143']));

        $otp = pregnantOtpFor('085730676143');
        expect($otp['code'])->not->toBeNull();
        expect($otp['request_id'])->not->toBeNull();
    });

    test('verifies OTP and logs in newly registered pregnant user', function () {
        $this->post(route('auth.pregnant.otp.send'), ['phone_number' => '85730676143']);
        $otp = pregnantOtpFor('085730676143');

        $response = $this->post(route('auth.pregnant.otp.verify'), [
            'phone_number' => '85730676143', // user verifies with same format
            'otp_request_id' => $otp['request_id'],
            'otp_code' => $otp['code'],
        ]);

        // New pregnant user needs to fill their name
        $response->assertRedirect(route('auth.pregnant.name.show'));

        $this->assertDatabaseHas('pregnant_users', [
            'phone_number' => '085730676143',
        ]);

        $user = PregnantUser::where('phone_number', '085730676143')->first();
        $this->assertAuthenticatedAs($user, 'pregnant');
    });

    test('allows user to save their full name', function () {
        $user = PregnantUser::factory()->create([
            'phone_number' => '085730676143',
            'full_name' => '',
        ]);

        $this->actingAs($user, 'pregnant')
            ->post(route('auth.pregnant.name.save'), [
                'full_name' => 'Yanti Suranti',
            ])
            ->assertRedirect(route('kehamilan.registrasi.show'));

        expect($user->fresh()->full_name)->toBe('Yanti Suranti');
    });

    test('registers pregnant user via web form with password and allows login with password', function () {
        $this->post(route('auth.pregnant.otp.send'), [
            'full_name' => 'Dewi Sartika',
            'phone_number' => '85730676143',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ])->assertRedirect(route('auth.pregnant.verify.show', ['phone' => '085730676143']));

        $otp = pregnantOtpFor('085730676143');

        $this->post(route('auth.pregnant.otp.verify'), [
            'phone_number' => '085730676143',
            'otp_request_id' => $otp['request_id'],
            'otp_code' => $otp['code'],
        ])->assertRedirect(route('auth.staff.login.show'))
          ->assertSessionHas('status');

        $this->assertDatabaseHas('pregnant_users', [
            'phone_number' => '085730676143',
            'full_name' => 'Dewi Sartika',
        ]);

        $user = PregnantUser::where('phone_number', '085730676143')->first();
        expect(Hash::check('secret1234', $user->password_hash))->toBeTrue();

        // Now user can login using password at the login page
        $loginResponse = $this->post(route('auth.staff.login'), [
            'identifier' => '085730676143',
            'password' => 'secret1234',
        ]);

        $loginResponse->assertRedirect(route('kehamilan.beranda'));
        $this->assertAuthenticatedAs($user, 'pregnant');
    });
});
