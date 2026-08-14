<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'full_name',
        'email',
        'password_hash',
        'institution',
    ];

    protected $hidden = [
        'password_hash',
    ];

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function verifiedWorkers(): HasMany
    {
        return $this->hasMany(HealthcareWorker::class, 'verified_by_admin_id');
    }

    public function overrideLogs(): HasMany
    {
        return $this->hasMany(AdminOverrideLog::class, 'admin_id');
    }
}
