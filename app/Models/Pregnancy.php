<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pregnancy extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pregnant_user_id',
        'mother_name',
        'estimated_due_date',
        'hpl_is_estimated',
        'gestational_age_weeks_at_registration',
        'is_twin_pregnancy',
        'has_prior_cesarean',
        'has_gestational_diabetes',
        'has_chronic_hypertension',
        'other_medical_conditions',
        'medical_notes',
        'region_code',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'status',
        'nifas_started_at',
        'nifas_marked_at',
        'delivery_notes',
        'case_closed_at',
        'case_closed_by',
    ];

    protected $attributes = [
        'status' => 'hamil',
        'is_twin_pregnancy' => false,
        'has_prior_cesarean' => false,
        'has_gestational_diabetes' => false,
        'has_chronic_hypertension' => false,
        'hpl_is_estimated' => false,
    ];

    protected function casts(): array
    {
        return [
            'estimated_due_date' => 'date',
            'hpl_is_estimated' => 'boolean',
            'is_twin_pregnancy' => 'boolean',
            'has_prior_cesarean' => 'boolean',
            'has_gestational_diabetes' => 'boolean',
            'has_chronic_hypertension' => 'boolean',
            'other_medical_conditions' => 'array',
            'nifas_started_at' => 'datetime',
            'nifas_marked_at' => 'datetime',
            'case_closed_at' => 'datetime',
        ];
    }

    public function pregnantUser(): BelongsTo
    {
        return $this->belongsTo(PregnantUser::class);
    }

    public function caseClosedBy(): BelongsTo
    {
        return $this->belongsTo(HealthcareWorker::class, 'case_closed_by');
    }

    public function consents(): HasMany
    {
        return $this->hasMany(Consent::class);
    }

    public function latestConsent(): HasOne
    {
        return $this->hasOne(Consent::class)->latestOfMany();
    }

    /** Flows.md §19.2: skrining/alert baru butuh consent aktif (belum dicabut). */
    public function hasActiveConsent(): bool
    {
        // Belum pernah ada baris consent sama sekali dianggap aktif (bukan
        // diblokir) — di alur nyata RegisterPregnancyAction selalu bikin
        // consent bareng pregnancy, jadi ini cuma jaga-jaga data lama/tidak
        // lengkap, bukan syarat baru buat pakai fitur.
        return $this->latestConsent?->revoked_at === null;
    }

    public function midwifeAssignments(): HasMany
    {
        return $this->hasMany(MidwifeAssignment::class);
    }

    public function activeMidwifeAssignment(): HasOne
    {
        return $this->hasOne(MidwifeAssignment::class)->where('is_active', true);
    }

    public function screeningSessions(): HasMany
    {
        return $this->hasMany(ScreeningSession::class);
    }

    public function riskAssessments(): HasMany
    {
        return $this->hasMany(RiskAssessment::class);
    }

    public function latestRiskAssessment(): HasOne
    {
        return $this->hasOne(RiskAssessment::class)->latestOfMany('assessed_at');
    }

    /** Beranda §3.6: usia kehamilan berjalan, bukan snapshot saat registrasi. */
    public function currentGestationalAgeWeeks(): int
    {
        $weeksElapsed = intdiv($this->created_at->diffInDays(now()), 7);

        return min(42, $this->gestational_age_weeks_at_registration + $weeksElapsed);
    }

    public function emergencyAlerts(): HasMany
    {
        return $this->hasMany(EmergencyAlert::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }

    public function clinicalVisits(): HasMany
    {
        return $this->hasMany(ClinicalVisit::class);
    }

    public function postpartumAssessment(): HasOne
    {
        return $this->hasOne(PostpartumAssessment::class)->latestOfMany();
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->whereIn('status', ['hamil', 'nifas']);
    }
}
