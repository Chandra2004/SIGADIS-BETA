<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KaderAreaAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'kader_id',
        'region_code',
        'kader_priority',
    ];

    public function kader(): BelongsTo
    {
        return $this->belongsTo(HealthcareWorker::class, 'kader_id');
    }
}
