<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'region_code',
        'address',
        'phone_number',
        'latitude',
        'longitude',
        'hospital_class',
        'has_icu',
        'has_nicu',
        'nicu_bed_count',
        'ambulance_status',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'has_icu' => 'boolean',
            'has_nicu' => 'boolean',
        ];
    }

    /**
     * Jarak lurus (km) dari satu titik GPS — Haversine, cukup akurat buat
     * urutan pilihan faskes terdekat, bukan rute jalan sesungguhnya.
     */
    public function distanceFromKm(float $latitude, float $longitude): float
    {
        if ($this->latitude === null || $this->longitude === null) {
            return INF;
        }

        $earthRadiusKm = 6371;
        $latDelta = deg2rad((float) $this->latitude - $latitude);
        $lonDelta = deg2rad((float) $this->longitude - $longitude);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($latitude)) * cos(deg2rad((float) $this->latitude)) * sin($lonDelta / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }
}
