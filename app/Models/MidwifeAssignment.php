<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MidwifeAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'pregnancy_id',
        'midwife_id',
        'assignment_method',
        'is_active',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
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
