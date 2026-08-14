<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ScreeningSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'pregnancy_id',
        'session_type',
        'started_at',
        'completed_at',
        'is_complete',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'is_complete' => 'boolean',
        ];
    }

    public function pregnancy(): BelongsTo
    {
        return $this->belongsTo(Pregnancy::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ScreeningAnswer::class);
    }

    public function riskAssessment(): HasOne
    {
        return $this->hasOne(RiskAssessment::class);
    }
}
