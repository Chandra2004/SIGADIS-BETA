<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RiskAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'pregnancy_id',
        'screening_session_id',
        'risk_level',
        'triggered_rule_codes',
        'is_data_incomplete',
        'recommendation_text',
        'disclaimer_shown',
        'assessed_at',
    ];

    protected $attributes = [
        'disclaimer_shown' => true,
    ];

    protected function casts(): array
    {
        return [
            'triggered_rule_codes' => 'array',
            'is_data_incomplete' => 'boolean',
            'disclaimer_shown' => 'boolean',
            'assessed_at' => 'datetime',
        ];
    }

    public function pregnancy(): BelongsTo
    {
        return $this->belongsTo(Pregnancy::class);
    }

    public function screeningSession(): BelongsTo
    {
        return $this->belongsTo(ScreeningSession::class);
    }

    public function emergencyAlert(): HasOne
    {
        return $this->hasOne(EmergencyAlert::class);
    }
}
