<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            [
                'name' => 'Puskesmas Sungai Raya',
                'type' => 'puskesmas',
                'hospital_class' => null,
                'has_icu' => false,
                'has_nicu' => false,
                'nicu_bed_count' => 0,
                'ambulance_status' => 'siaga',
                'region_code' => '33.08.05.2009',
                'address' => 'Jl. Adi Sucipto, Sungai Raya',
                'phone_number' => '0561-711234',
                'latitude' => -0.0833,
                'longitude' => 109.3500,
            ],
            [
                'name' => 'RSUD Soedarso Pontianak',
                'type' => 'rumah_sakit',
                'hospital_class' => 'B',
                'has_icu' => true,
                'has_nicu' => true,
                'nicu_bed_count' => 8,
                'ambulance_status' => 'siaga',
                'region_code' => '33.08.05.2009',
                'address' => 'Jl. Dr. Sukardjo No. 1, Pontianak',
                'phone_number' => '0561-737701',
                'latitude' => -0.0263,
                'longitude' => 109.3425,
            ],
            [
                'name' => 'Polindes Melati Ambangah',
                'type' => 'polindes',
                'hospital_class' => null,
                'has_icu' => false,
                'has_nicu' => false,
                'nicu_bed_count' => 0,
                'ambulance_status' => 'siaga',
                'region_code' => '33.08.05.2010',
                'address' => 'Jl. Melati Desa Sungai Ambangah',
                'phone_number' => '081234567890',
                'latitude' => -0.0950,
                'longitude' => 109.3610,
            ],
            [
                'name' => 'Klinik Pratama Bersalin Kasih Ibu',
                'type' => 'klinik',
                'hospital_class' => null,
                'has_icu' => false,
                'has_nicu' => false,
                'nicu_bed_count' => 0,
                'ambulance_status' => 'siaga',
                'region_code' => '33.08.05.2001',
                'address' => 'Jl. Merdeka No. 45, Desa Parit Baru',
                'phone_number' => '0561-744889',
                'latitude' => -0.0815,
                'longitude' => 109.3522,
            ],
        ];

        foreach ($facilities as $facility) {
            Facility::updateOrCreate(
                ['name' => $facility['name'], 'region_code' => $facility['region_code']],
                $facility
            );
        }
    }
}
