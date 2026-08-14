<?php

use App\Models\PregnantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('lets a pregnant user upload a profile photo', function () {
    Storage::fake('public');
    $user = PregnantUser::factory()->create();

    $this->actingAs($user, 'pregnant')
        ->post(route('kehamilan.pengaturan.foto.update'), [
            'photo' => UploadedFile::fake()->image('foto.jpg'),
        ])
        ->assertRedirect();

    $fresh = $user->fresh();
    expect($fresh->profile_photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($fresh->profile_photo_path);
});

it('replaces the old photo file when uploading a new one', function () {
    Storage::fake('public');
    $user = PregnantUser::factory()->create();

    $this->actingAs($user, 'pregnant')->post(route('kehamilan.pengaturan.foto.update'), [
        'photo' => UploadedFile::fake()->image('lama.jpg'),
    ]);
    $oldPath = $user->fresh()->profile_photo_path;

    $this->actingAs($user, 'pregnant')->post(route('kehamilan.pengaturan.foto.update'), [
        'photo' => UploadedFile::fake()->image('baru.jpg'),
    ]);

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($user->fresh()->profile_photo_path);
});

it('rejects a non-image file', function () {
    Storage::fake('public');
    $user = PregnantUser::factory()->create();

    $this->actingAs($user, 'pregnant')
        ->post(route('kehamilan.pengaturan.foto.update'), [
            'photo' => UploadedFile::fake()->create('dokumen.pdf', 100),
        ])
        ->assertSessionHasErrors('photo');
});

it('lets a pregnant user remove their profile photo', function () {
    Storage::fake('public');
    $user = PregnantUser::factory()->create();
    $this->actingAs($user, 'pregnant')->post(route('kehamilan.pengaturan.foto.update'), [
        'photo' => UploadedFile::fake()->image('foto.jpg'),
    ]);
    $path = $user->fresh()->profile_photo_path;

    $this->actingAs($user, 'pregnant')->delete(route('kehamilan.pengaturan.foto.destroy'))->assertRedirect();

    expect($user->fresh()->profile_photo_path)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

it('shares the profile photo url on the beranda page', function () {
    Storage::fake('public');
    $user = PregnantUser::factory()->create();
    $user->pregnancies()->create(['mother_name' => 'Ibu Foto', 'gestational_age_weeks_at_registration' => 20, 'region_code' => 'A']);
    $this->actingAs($user, 'pregnant')->post(route('kehamilan.pengaturan.foto.update'), [
        'photo' => UploadedFile::fake()->image('foto.jpg'),
    ]);

    $response = $this->actingAs($user, 'pregnant')->get(route('kehamilan.beranda'));

    expect($response->viewData('page')['props']['profilePhotoUrl'])->not->toBeNull();
});
