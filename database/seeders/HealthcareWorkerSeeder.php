<?php

namespace Database\Seeders;

use App\Models\HealthcareWorker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HealthcareWorkerSeeder extends Seeder
{
    public function run(): void
    {
        HealthcareWorker::updateOrCreate(
            ['phone_number' => '081234500001'],
            [
                'full_name' => 'Bidan Siti Aminah',
                'password_hash' => Hash::make('password'),
                'role' => 'bidan',
                'str_number' => 'STR-0001-2026',
                'status' => 'verified',
                'verified_at' => now(),
                'region_code' => '33.08.05.2009',
                'is_available' => true,
            ]
        );

        $kader = HealthcareWorker::updateOrCreate(
            ['phone_number' => '081234500002'],
            [
                'full_name' => 'Kader Ratna Dewi',
                'password_hash' => Hash::make('password'),
                'role' => 'kader',
                'appointment_letter_ref' => 'SK-DESA-0007-2026',
                'status' => 'verified',
                'verified_at' => now(),
                'region_code' => '33.08.05.2009',
                'is_available' => true,
            ]
        );

        $kader->kaderAreaAssignments()->updateOrCreate(
            ['region_code' => '33.08.05.2009'],
            ['kader_priority' => 'primary']
        );
    }
}
