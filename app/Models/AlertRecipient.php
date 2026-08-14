<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'emergency_alert_id',
        'healthcare_worker_id',
        'recipient_role_at_time',
        'delivery_status',
        'sent_at',
        'retry_count',
        'acknowledged_at',
    ];

    protected $attributes = [
        'delivery_status' => 'pending',
        'retry_count' => 0,
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'acknowledged_at' => 'datetime',
        ];
    }

    public function alert(): BelongsTo
    {
        return $this->belongsTo(EmergencyAlert::class, 'emergency_alert_id');
    }

    public function healthcareWorker(): BelongsTo
    {
        return $this->belongsTo(HealthcareWorker::class);
    }
}
