<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class PregnantUser extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'phone_number',
        'full_name',
        'profile_photo_path',
        'otp_verified_at',
        'text_size',
        'tts_enabled',
        'screening_reminder_enabled',
        'gps_permission_enabled',
        'share_data_with_midwife_enabled',
    ];

    protected $hidden = [];

    protected function casts(): array
    {
        return [
            'otp_verified_at' => 'datetime',
            'tts_enabled' => 'boolean',
            'screening_reminder_enabled' => 'boolean',
            'gps_permission_enabled' => 'boolean',
            'share_data_with_midwife_enabled' => 'boolean',
        ];
    }

    public function profilePhotoUrl(): ?string
    {
        return $this->profile_photo_path ? Storage::disk('public')->url($this->profile_photo_path) : null;
    }

    public function pregnancies(): HasMany
    {
        return $this->hasMany(Pregnancy::class);
    }

    public function overrideLogs(): HasMany
    {
        return $this->hasMany(AdminOverrideLog::class);
    }
}
