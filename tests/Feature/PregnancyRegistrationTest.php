<?php

use App\Models\HealthcareWorker;
use App\Models\PregnantUser;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

function otpFor(string $phone): array
{
    $otp = app(OtpService::class);

    return [
        'code' => Cache::get($otp->codeKey($phone)),
        'request_id' => Cache::get($otp->requestId($phone)),
    ];
}

it('lets a new pregnant user register via OTP then complete pregnancy registration', function () {
    $phone = '081298765432';

    $midwife = HealthcareWorker::factory()->create([
        'role' => 'bidan',
        'status' => 'verified',
        'is_available' => true,
        'region_code' => '33.08.05.2009',
    ]);

    // 1. Kirim OTP
    $this->post(route('auth.pregnant.otp.send'), ['phone_number' => $phone])
        ->assertRedirect(route('auth.pregnant.verify.show', ['phone' => $phone]));

    $otp = otpFor($phone);
    expect($otp['code'])->not->toBeNull();

    // 2. Verifikasi OTP salah -> ditolak
    $this->post(route('auth.pregnant.otp.verify'), [
        'phone_number' => $phone,
        'otp_request_id' => $otp['request_id'],
        'otp_code' => '000000',
    ])->assertSessionHasErrors('otp_code');

    $this->assertGuest('pregnant');

    // 3. Verifikasi OTP benar -> login, akun baru diarahkan isi nama
    $this->post(route('auth.pregnant.otp.verify'), [
        'phone_number' => $phone,
        'otp_request_id' => $otp['request_id'],
        'otp_code' => $otp['code'],
    ])->assertRedirect(route('auth.pregnant.name.show'));

    $this->assertAuthenticated('pregnant');

    // 4. Isi nama
    $this->post(route('auth.pregnant.name.save'), ['full_name' => 'Suami Ibu Ani'])
        ->assertRedirect(route('kehamilan.registrasi.show'));

    expect(PregnantUser::where('phone_number', $phone)->first()->full_name)->toBe('Suami Ibu Ani');

    // 5. Registrasi tanpa consent ditolak (defense-in-depth backend)
    $this->post(route('kehamilan.registrasi.store'), [
        'mother_name' => 'Ani',
        'hpl_is_estimated' => true,
        'gestational_age_weeks_at_registration' => 20,
        'is_twin_pregnancy' => false,
        'has_prior_cesarean' => false,
        'has_gestational_diabetes' => false,
        'has_chronic_hypertension' => false,
        'region_code' => '33.08.05.2009',
        'consent_granted' => false,
        'consent_version' => '1.0',
    ])->assertSessionHasErrors('consent_granted');

    // 6. Registrasi lengkap dengan consent aktif -> auto-pairing bidan
    $response = $this->post(route('kehamilan.registrasi.store'), [
        'mother_name' => 'Ani',
        'hpl_is_estimated' => true,
        'gestational_age_weeks_at_registration' => 20,
        'is_twin_pregnancy' => false,
        'has_prior_cesarean' => false,
        'has_gestational_diabetes' => false,
        'has_chronic_hypertension' => false,
        'region_code' => '33.08.05.2009',
        'consent_granted' => true,
        'consent_version' => '1.0',
    ]);
    $pregnancy = PregnantUser::where('phone_number', $phone)->first()->pregnancies()->first();
    $response->assertRedirect(route('kehamilan.registrasi.sukses', $pregnancy));

    expect($pregnancy->mother_name)->toBe('Ani')
        ->and($pregnancy->consents()->count())->toBe(1)
        ->and($pregnancy->activeMidwifeAssignment->midwife_id)->toBe($midwife->id)
        ->and($pregnancy->activeMidwifeAssignment->assignment_method)->toBe('auto_zonasi');
});

it('registers a pregnancy without a midwife when none is available in the region (fail-safe)', function () {
    $phone = '081211112222';
    $user = PregnantUser::factory()->create(['phone_number' => $phone]);

    $response = $this->actingAs($user, 'pregnant')
        ->post(route('kehamilan.registrasi.store'), [
            'mother_name' => 'Budi',
            'hpl_is_estimated' => true,
            'gestational_age_weeks_at_registration' => 10,
            'is_twin_pregnancy' => false,
            'has_prior_cesarean' => false,
            'has_gestational_diabetes' => false,
            'has_chronic_hypertension' => false,
            'region_code' => '00.00.00.0000',
            'consent_granted' => true,
            'consent_version' => '1.0',
        ]);

    $pregnancy = $user->pregnancies()->first();
    $response->assertRedirect(route('kehamilan.registrasi.sukses', $pregnancy));

    expect($pregnancy)->not->toBeNull()
        ->and($pregnancy->activeMidwifeAssignment)->toBeNull();
});

it('shows the registration success summary page after completing registration', function () {
    $midwife = HealthcareWorker::factory()->create(['role' => 'bidan', 'status' => 'verified', 'region_code' => 'Y']);
    $user = PregnantUser::factory()->create(['gps_permission_enabled' => true]);

    $this->actingAs($user, 'pregnant')->post(route('kehamilan.registrasi.store'), [
        'mother_name' => 'Sari', 'hpl_is_estimated' => true, 'gestational_age_weeks_at_registration' => 15,
        'is_twin_pregnancy' => false, 'has_prior_cesarean' => false, 'has_gestational_diabetes' => false,
        'has_chronic_hypertension' => false, 'region_code' => 'Y', 'consent_granted' => true, 'consent_version' => '1.0',
    ]);

    $pregnancy = $user->pregnancies()->first();

    $response = $this->actingAs($user, 'pregnant')->get(route('kehamilan.registrasi.sukses', $pregnancy));

    $response->assertSuccessful();
    $props = $response->viewData('page')['props'];
    expect($props['motherName'])->toBe('Sari')
        ->and($props['midwifeName'])->toBe($midwife->full_name)
        ->and($props['gpsPermissionEnabled'])->toBeTrue();
});

it('blocks viewing another user\'s registration success page', function () {
    $owner = PregnantUser::factory()->create();
    $pregnancy = $owner->pregnancies()->create(['mother_name' => 'X', 'gestational_age_weeks_at_registration' => 10, 'region_code' => 'Y']);
    $intruder = PregnantUser::factory()->create();

    $this->actingAs($intruder, 'pregnant')->get(route('kehamilan.registrasi.sukses', $pregnancy))->assertForbidden();
});

it('rate limits repeated OTP send requests for the same phone number', function () {
    $phone = '081200001111';

    for ($i = 0; $i < 3; $i++) {
        $this->post(route('auth.pregnant.otp.send'), ['phone_number' => $phone])
            ->assertRedirect(route('auth.pregnant.verify.show', ['phone' => $phone]));
    }

    $this->post(route('auth.pregnant.otp.send'), ['phone_number' => $phone])
        ->assertSessionHasErrors('phone_number');
});
