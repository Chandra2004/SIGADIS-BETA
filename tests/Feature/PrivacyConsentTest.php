<?php

use App\Console\Commands\ProcessDataDeletionRequests;
use App\Models\Pregnancy;
use App\Models\PregnantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function pregnancyWithConsent(): array
{
    $user = PregnantUser::factory()->create();
    $pregnancy = $user->pregnancies()->create([
        'mother_name' => 'Ibu Privasi', 'gestational_age_weeks_at_registration' => 20, 'region_code' => 'A',
    ]);
    $pregnancy->consents()->create(['consent_version' => '1.0', 'granted_at' => now()]);

    return [$user, $pregnancy];
}

it('shows the privacy page with active consent by default', function () {
    [$user, $pregnancy] = pregnancyWithConsent();

    $response = $this->actingAs($user, 'pregnant')->get(route('kehamilan.privasi'));

    $response->assertSuccessful();
    expect($response->viewData('page')['props']['consentActive'])->toBeTrue();
});

it('lets the mother revoke consent, blocking new screening but keeping the emergency button available', function () {
    [$user, $pregnancy] = pregnancyWithConsent();

    $this->actingAs($user, 'pregnant')
        ->post(route('kehamilan.privasi.revoke-consent'))
        ->assertRedirect();

    expect($pregnancy->latestConsent->fresh()->revoked_at)->not->toBeNull();

    $this->actingAs($user, 'pregnant')
        ->post(route('skrining.mulai'), ['session_type' => 'initial'])
        ->assertForbidden();

    // FAB darurat tetap jalan meski consent dicabut (Flows.md §19.2.3).
    $this->actingAs($user, 'pregnant')
        ->post(route('darurat.aktivasi'))
        ->assertRedirect(route('darurat.status'));
});

it('lets the mother reactivate consent with a new consent row, keeping the old revocation as history', function () {
    [$user, $pregnancy] = pregnancyWithConsent();
    $this->actingAs($user, 'pregnant')->post(route('kehamilan.privasi.revoke-consent'));
    $oldConsentId = $pregnancy->latestConsent->id;

    $this->actingAs($user, 'pregnant')
        ->post(route('kehamilan.privasi.reactivate-consent'))
        ->assertRedirect();

    expect($pregnancy->fresh()->hasActiveConsent())->toBeTrue()
        ->and($pregnancy->consents()->count())->toBe(2);

    // Consent lama tetap ada dengan revoked_at terisi, bukan di-undo.
    expect($pregnancy->consents()->find($oldConsentId)->revoked_at)->not->toBeNull();

    $this->actingAs($user, 'pregnant')
        ->post(route('skrining.mulai'), ['session_type' => 'initial'])
        ->assertRedirect();
});

it('rejects a data deletion request without the exact "HAPUS" confirmation', function () {
    [$user, $pregnancy] = pregnancyWithConsent();

    $this->actingAs($user, 'pregnant')
        ->post(route('kehamilan.privasi.request-deletion'), ['confirmation' => 'hapus'])
        ->assertSessionHasErrors('confirmation');

    expect($pregnancy->latestConsent->fresh()->data_deletion_requested_at)->toBeNull();
});

it('accepts a data deletion request and anonymizes the pregnancy once processed with no open alert', function () {
    [$user, $pregnancy] = pregnancyWithConsent();

    $this->actingAs($user, 'pregnant')
        ->post(route('kehamilan.privasi.request-deletion'), ['confirmation' => 'HAPUS'])
        ->assertRedirect();

    expect($pregnancy->latestConsent->fresh()->data_deletion_requested_at)->not->toBeNull()
        ->and($pregnancy->fresh()->trashed())->toBeFalse();

    $this->artisan(ProcessDataDeletionRequests::class)->assertSuccessful();

    $anonymized = Pregnancy::withTrashed()->find($pregnancy->id);
    expect($anonymized->mother_name)->toBe('Data Dihapus')
        ->and($anonymized->trashed())->toBeTrue();
});

it('defers deletion processing while an emergency alert on the pregnancy is still open', function () {
    [$user, $pregnancy] = pregnancyWithConsent();
    $riskAssessment = $pregnancy->riskAssessments()->create([
        'risk_level' => 'tinggi', 'triggered_rule_codes' => ['bleeding_heavy'],
        'recommendation_text' => 'Segera ke faskes.', 'assessed_at' => now(),
    ]);
    $pregnancy->emergencyAlerts()->create([
        'trigger_type' => 'manual_button', 'risk_assessment_id' => $riskAssessment->id,
        'status' => 'pending', 'triggered_at' => now(),
    ]);

    $this->actingAs($user, 'pregnant')->post(route('kehamilan.privasi.request-deletion'), ['confirmation' => 'HAPUS']);

    $this->artisan(ProcessDataDeletionRequests::class)->assertSuccessful();

    expect($pregnancy->fresh()->trashed())->toBeFalse();
});
