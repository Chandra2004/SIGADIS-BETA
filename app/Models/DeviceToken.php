<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'healthcare_worker_id',
        'fcm_token',
        'platform',
        'registered_at',
        'last_seen_at',
        'is_active',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function healthcareWorker(): BelongsTo
    {
        return $this->belongsTo(HealthcareWorker::class);
    }
}
