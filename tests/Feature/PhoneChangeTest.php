<?php

use App\Models\PregnantUser;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

function phoneChangeOtpFor(string $phone): array
{
    $otp = app(OtpService::class);

    return [
        'code' => Cache::get($otp->codeKey($phone)),
        'request_id' => Cache::get($otp->requestId($phone)),
    ];
}

it('lets a pregnant user change their phone number end to end after verifying both numbers', function () {
    $user = PregnantUser::factory()->create(['phone_number' => '081211110000']);

    $this->actingAs($user, 'pregnant')->post(route('akun.ganti-nomor.send-old'))->assertRedirect();
    $oldOtp = phoneChangeOtpFor('081211110000');

    $this->actingAs($user, 'pregnant')
        ->post(route('akun.ganti-nomor.verify-old'), ['otp_request_id' => $oldOtp['request_id'], 'otp_code' => $oldOtp['code']])
        ->assertRedirect();

    $this->actingAs($user, 'pregnant')
        ->post(route('akun.ganti-nomor.send-new'), ['new_phone_number' => '081299998888'])
        ->assertRedirect();
    $newOtp = phoneChangeOtpFor('081299998888');

    $this->actingAs($user, 'pregnant')
        ->post(route('akun.ganti-nomor.verify-new'), [
            'new_phone_number' => '081299998888',
            'otp_request_id' => $newOtp['request_id'],
            'otp_code' => $newOtp['code'],
        ])
        ->assertRedirect(route('kehamilan.beranda'));

    expect($user->fresh()->phone_number)->toBe('081299998888');
});

it('blocks sending a new-number OTP before the old number is verified', function () {
    $user = PregnantUser::factory()->create(['phone_number' => '081211110000']);

    $this->actingAs($user, 'pregnant')
        ->post(route('akun.ganti-nomor.send-new'), ['new_phone_number' => '081299998888'])
        ->assertForbidden();
});

it('rejects a new phone number already registered to another account without leaking details', function () {
    $user = PregnantUser::factory()->create(['phone_number' => '081211110000']);
    PregnantUser::factory()->create(['phone_number' => '081299998888']);

    $this->actingAs($user, 'pregnant')->post(route('akun.ganti-nomor.send-old'));
    $oldOtp = phoneChangeOtpFor('081211110000');
    $this->actingAs($user, 'pregnant')->post(route('akun.ganti-nomor.verify-old'), [
        'otp_request_id' => $oldOtp['request_id'], 'otp_code' => $oldOtp['code'],
    ]);

    $response = $this->actingAs($user, 'pregnant')
        ->post(route('akun.ganti-nomor.send-new'), ['new_phone_number' => '081299998888']);

    $response->assertSessionHasErrors('new_phone_number');
    expect(session()->get('errors')->get('new_phone_number')[0])->not->toContain('account');
});
