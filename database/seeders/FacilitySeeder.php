<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            ['name' => 'Puskesmas Sungai Raya', 'type' => 'puskesmas', 'region_code' => '33.08.05.2009', 'address' => 'Jl. Adi Sucipto, Sungai Raya', 'phone_number' => '0561-711234', 'latitude' => -0.0833, 'longitude' => 109.3500],
            ['name' => 'RSUD Soedarso', 'type' => 'rumah_sakit', 'region_code' => '33.08.05.2009', 'address' => 'Jl. Dr. Sukardjo No. 1, Pontianak', 'phone_number' => '0561-737701', 'latitude' => -0.0263, 'longitude' => 109.3425],
            ['name' => 'Polindes Melati', 'type' => 'polindes', 'region_code' => '33.08.05.2010', 'address' => 'Jl. Melati Desa Sungai Ambangah', 'phone_number' => null, 'latitude' => -0.0950, 'longitude' => 109.3610],
        ];

        foreach ($facilities as $facility) {
            Facility::updateOrCreate(
                ['name' => $facility['name'], 'region_code' => $facility['region_code']],
                $facility
            );
        }
    }
}
