<?php

use App\Models\PregnantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows default app settings with no language option', function () {
    $user = PregnantUser::factory()->create();

    $response = $this->actingAs($user, 'pregnant')->get(route('kehamilan.pengaturan'));

    $response->assertSuccessful();
    $settings = $response->viewData('page')['props']['settings'];
    expect($settings)->toHaveKeys(['text_size', 'tts_enabled', 'screening_reminder_enabled'])
        ->and($settings)->not->toHaveKey('language')
        ->and($settings)->not->toHaveKey('education_updates_enabled');
});

it('lets a pregnant user update their app settings', function () {
    $user = PregnantUser::factory()->create();

    $this->actingAs($user, 'pregnant')
        ->post(route('kehamilan.pengaturan.update'), [
            'text_size' => 'besar',
            'tts_enabled' => false,
            'screening_reminder_enabled' => false,
        ])
        ->assertRedirect();

    $fresh = $user->fresh();
    expect($fresh->text_size)->toBe('besar')
        ->and($fresh->tts_enabled)->toBeFalse()
        ->and($fresh->screening_reminder_enabled)->toBeFalse();
});

it('shares text_size as a top-level prop so it applies app-wide, not only on the settings page', function () {
    $user = PregnantUser::factory()->create(['text_size' => 'besar']);

    $response = $this->actingAs($user, 'pregnant')->get(route('kehamilan.beranda'));

    expect($response->viewData('page')['props']['textSize'])->toBe('besar');
});

it('rejects an invalid text size value', function () {
    $user = PregnantUser::factory()->create();

    $this->actingAs($user, 'pregnant')
        ->post(route('kehamilan.pengaturan.update'), [
            'text_size' => 'raksasa',
            'tts_enabled' => true,
            'screening_reminder_enabled' => true,
        ])
        ->assertSessionHasErrors('text_size');
});
