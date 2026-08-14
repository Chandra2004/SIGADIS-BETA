<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostpartumAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'pregnancy_id',
        'midwife_id',
        'physical_recovery_status',
        'infant_growth_status',
        'infant_weight_kg',
        'family_planning_status',
        'family_planning_method',
        'next_steps',
        'final_summary_note',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'infant_weight_kg' => 'decimal:2',
            'confirmed_at' => 'datetime',
        ];
    }

    public function pregnancy(): BelongsTo
    {
        return $this->belongsTo(Pregnancy::class);
    }

    public function midwife(): BelongsTo
    {
        return $this->belongsTo(HealthcareWorker::class, 'midwife_id');
    }
}
