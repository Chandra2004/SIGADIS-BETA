<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'pregnancy_id',
        'midwife_id',
        'visit_type',
        'status_tag',
        'blood_pressure_systolic',
        'blood_pressure_diastolic',
        'symptoms',
        'clinical_notes',
        'visited_at',
    ];

    protected function casts(): array
    {
        return [
            'symptoms' => 'array',
            'visited_at' => 'datetime',
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
