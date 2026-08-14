<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertHandlingCancellation extends Model
{
    use HasFactory;

    protected $fillable = [
        'emergency_alert_id',
        'cancelled_handler_id',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'cancelled_at' => 'datetime',
        ];
    }

    public function alert(): BelongsTo
    {
        return $this->belongsTo(EmergencyAlert::class, 'emergency_alert_id');
    }

    public function cancelledHandler(): BelongsTo
    {
        return $this->belongsTo(HealthcareWorker::class, 'cancelled_handler_id');
    }
}
