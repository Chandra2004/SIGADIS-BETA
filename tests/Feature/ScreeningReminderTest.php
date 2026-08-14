<?php

use App\Models\PregnantUser;
use App\Notifications\ScreeningReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

function pregnancyDueForReminder(array $overrides = []): array
{
    Config::set('screening.reminder_interval_days', 14);

    $user = PregnantUser::factory()->create(array_merge(['screening_reminder_enabled' => true], $overrides));
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Reminder', 'gestational_age_weeks_at_registration' => 24, 'region_code' => 'X',
    ]);
    $pregnancy->screeningSessions()->create([
        'session_type' => 'initial', 'started_at' => now()->subDays(20), 'completed_at' => now()->subDays(20), 'is_complete' => true,
    ]);

    return [$user, $pregnancy];
}

it('sends a screening reminder when the interval has passed', function () {
    [$user] = pregnancyDueForReminder();

    $this->artisan('screening:send-reminders')->assertSuccessful();

    expect($user->fresh()->notifications()->where('type', ScreeningReminderNotification::class)->count())->toBe(1);
});

it('does not send a reminder before the interval has passed', function () {
    $user = PregnantUser::factory()->create(['screening_reminder_enabled' => true]);
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Baru', 'gestational_age_weeks_at_registration' => 24, 'region_code' => 'X',
    ]);
    $pregnancy->screeningSessions()->create([
        'session_type' => 'initial', 'started_at' => now()->subDays(2), 'completed_at' => now()->subDays(2), 'is_complete' => true,
    ]);

    $this->artisan('screening:send-reminders');

    expect($user->fresh()->notifications()->where('type', ScreeningReminderNotification::class)->count())->toBe(0);
});

it('does not send a reminder when the user has disabled it', function () {
    [$user] = pregnancyDueForReminder(['screening_reminder_enabled' => false]);

    $this->artisan('screening:send-reminders');

    expect($user->fresh()->notifications()->where('type', ScreeningReminderNotification::class)->count())->toBe(0);
});

it('does not send a duplicate reminder on a second run within the same due window', function () {
    [$user] = pregnancyDueForReminder();

    $this->artisan('screening:send-reminders');
    $this->artisan('screening:send-reminders');

    expect($user->fresh()->notifications()->where('type', ScreeningReminderNotification::class)->count())->toBe(1);
});

it('does not remind a pregnancy with an open, unfinished screening session', function () {
    [$user, $pregnancy] = pregnancyDueForReminder();
    $pregnancy->screeningSessions()->create(['session_type' => 'periodic', 'started_at' => now(), 'is_complete' => false]);

    $this->artisan('screening:send-reminders');

    expect($user->fresh()->notifications()->where('type', ScreeningReminderNotification::class)->count())->toBe(0);
});
