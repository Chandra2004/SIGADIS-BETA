<?php

use App\Models\Pregnancy;
use App\Models\PregnantUser;
use App\Services\RiskAssessmentEngine;
use Database\Seeders\ScreeningQuestionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed(ScreeningQuestionSeeder::class));

function pregnancy(array $overrides = []): Pregnancy
{
    return PregnantUser::factory()->create()->pregnancies()->create(array_merge([
        'mother_name' => 'Ibu Uji',
        'gestational_age_weeks_at_registration' => 24,
        'region_code' => '33.08.05.2009',
    ], $overrides));
}

it('classifies as tinggi when a single critical symptom is answered ya', function () {
    $result = app(RiskAssessmentEngine::class)->evaluate(pregnancy(), collect(['bleeding_heavy' => 'ya']));

    expect($result['risk_level'])->toBe('tinggi')
        ->and($result['triggered_rule_codes'])->toContain('bleeding_heavy');
});

it('classifies as tinggi when the preeklamsia combo (2 gejala) is met', function () {
    $result = app(RiskAssessmentEngine::class)->evaluate(
        pregnancy(),
        collect(['headache_severe' => 'ya', 'vision_blurred' => 'ya', 'fever_high' => 'tidak'])
    );

    expect($result['risk_level'])->toBe('tinggi');
});

it('does not classify as tinggi from a single non-critical warning symptom alone', function () {
    $result = app(RiskAssessmentEngine::class)->evaluate(pregnancy(), collect(['headache_severe' => 'ya']));

    expect($result['risk_level'])->toBe('sedang');
});

it('lowers the preeklamsia combo threshold to 1 when the pregnancy has a chronic condition', function () {
    $result = app(RiskAssessmentEngine::class)->evaluate(
        pregnancy(['has_chronic_hypertension' => true]),
        collect(['headache_severe' => 'ya'])
    );

    expect($result['risk_level'])->toBe('tinggi');
});

it('lowers the bleeding/pain threshold for twin pregnancies', function () {
    $result = app(RiskAssessmentEngine::class)->evaluate(
        pregnancy(['is_twin_pregnancy' => true]),
        collect(['bleeding_spotting' => 'ya'])
    );

    expect($result['risk_level'])->toBe('tinggi');

    $resultSingleton = app(RiskAssessmentEngine::class)->evaluate(
        pregnancy(['is_twin_pregnancy' => false]),
        collect(['bleeding_spotting' => 'ya'])
    );

    expect($resultSingleton['risk_level'])->toBe('sedang');
});

it('classifies as rendah when no symptoms are ya', function () {
    $result = app(RiskAssessmentEngine::class)->evaluate(
        pregnancy(),
        collect(['bleeding_spotting' => 'tidak', 'headache_severe' => 'tidak'])
    );

    expect($result['risk_level'])->toBe('rendah');
});

it('manual activation is always tinggi regardless of answers', function () {
    $result = app(RiskAssessmentEngine::class)->manualActivation();

    expect($result['risk_level'])->toBe('tinggi');
});
