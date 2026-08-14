<?php

use App\Models\AdminOverrideLog;
use App\Models\AdminUser;
use App\Models\PregnantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lets admin search a pregnant user by phone number or name', function () {
    $admin = AdminUser::factory()->create();
    PregnantUser::factory()->create(['full_name' => 'Ibu Siti', 'phone_number' => '081211112222']);
    PregnantUser::factory()->create(['full_name' => 'Ibu Rina', 'phone_number' => '081233334444']);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.ganti-nomor.index', ['q' => 'Siti']));

    $response->assertSuccessful();
    expect($response->viewData('page')['props']['results'])->toHaveCount(1);
});

it('lets admin override a pregnant user\'s phone number with a reason, logging the change', function () {
    $admin = AdminUser::factory()->create();
    $user = PregnantUser::factory()->create(['phone_number' => '081200001111']);

    $this->actingAs($admin, 'admin')
        ->post(route('admin.ganti-nomor.store', $user), [
            'new_phone_number' => '081299998888',
            'reason' => 'Verifikasi tatap muka via bidan pendamping, KTP dicocokkan.',
        ])
        ->assertRedirect(route('admin.ganti-nomor.index'));

    expect($user->fresh()->phone_number)->toBe('081299998888');

    $log = AdminOverrideLog::first();
    expect($log)
        ->admin_id->toBe($admin->id)
        ->pregnant_user_id->toBe($user->id)
        ->old_phone_number->toBe('081200001111')
        ->new_phone_number->toBe('081299998888');
});

it('rejects a phone override without a reason', function () {
    $admin = AdminUser::factory()->create();
    $user = PregnantUser::factory()->create();

    $this->actingAs($admin, 'admin')
        ->post(route('admin.ganti-nomor.store', $user), ['new_phone_number' => '081299998888', 'reason' => 'singkat'])
        ->assertSessionHasErrors('reason');
});

it('rejects a phone override to a number already in use', function () {
    $admin = AdminUser::factory()->create();
    $user = PregnantUser::factory()->create();
    $other = PregnantUser::factory()->create(['phone_number' => '081277778888']);

    $this->actingAs($admin, 'admin')
        ->post(route('admin.ganti-nomor.store', $user), [
            'new_phone_number' => '081277778888',
            'reason' => 'Verifikasi tatap muka, dokumen dicocokkan.',
        ])
        ->assertSessionHasErrors('new_phone_number');
});
