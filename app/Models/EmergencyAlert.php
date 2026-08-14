<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmergencyAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'pregnancy_id',
        'trigger_type',
        'risk_assessment_id',
        'status',
        'triggered_at',
        'latitude',
        'longitude',
        'handled_by_id',
        'handled_at',
        'escalated_to_kader_at',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'triggered_at' => 'datetime',
            'handled_at' => 'datetime',
            'escalated_to_kader_at' => 'datetime',
        ];
    }

    public function pregnancy(): BelongsTo
    {
        return $this->belongsTo(Pregnancy::class);
    }

    public function riskAssessment(): BelongsTo
    {
        return $this->belongsTo(RiskAssessment::class);
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(HealthcareWorker::class, 'handled_by_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(AlertRecipient::class);
    }

    public function cancellations(): HasMany
    {
        return $this->hasMany(AlertHandlingCancellation::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }

    #[Scope]
    protected function open(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'delivered', 'being_handled']);
    }
}
