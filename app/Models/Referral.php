<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'pregnancy_id',
        'emergency_alert_id',
        'facility_id',
        'referred_by_id',
        'referred_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'referred_at' => 'datetime',
        ];
    }

    public function pregnancy(): BelongsTo
    {
        return $this->belongsTo(Pregnancy::class);
    }

    public function emergencyAlert(): BelongsTo
    {
        return $this->belongsTo(EmergencyAlert::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(HealthcareWorker::class, 'referred_by_id');
    }
}
