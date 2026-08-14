<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class HealthcareWorker extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'full_name',
        'phone_number',
        'password_hash',
        'role',
        'str_number',
        'appointment_letter_ref',
        'status',
        'verified_by_admin_id',
        'verified_at',
        'admin_note',
        'region_code',
        'is_available',
        'unavailable_from',
        'unavailable_until',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $attributes = [
        'status' => 'pending',
        'is_available' => true,
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'unavailable_from' => 'date',
            'unavailable_until' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function verifiedByAdmin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'verified_by_admin_id');
    }

    public function midwifeAssignments(): HasMany
    {
        return $this->hasMany(MidwifeAssignment::class, 'midwife_id');
    }

    public function kaderAreaAssignments(): HasMany
    {
        return $this->hasMany(KaderAreaAssignment::class, 'kader_id');
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function handledAlerts(): HasMany
    {
        return $this->hasMany(EmergencyAlert::class, 'handled_by_id');
    }

    #[Scope]
    protected function verified(Builder $query): Builder
    {
        return $query->where('status', 'verified');
    }

    #[Scope]
    protected function available(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    #[Scope]
    protected function bidan(Builder $query): Builder
    {
        return $query->where('role', 'bidan');
    }

    #[Scope]
    protected function kader(Builder $query): Builder
    {
        return $query->where('role', 'kader');
    }

    #[Scope]
    protected function inRegion(Builder $query, string $regionCode): Builder
    {
        return $query->where('region_code', $regionCode);
    }
}
