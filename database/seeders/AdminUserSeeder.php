<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        AdminUser::updateOrCreate(
            ['email' => 'admin@sigadis.test'],
            [
                'full_name' => 'Admin Puskesmas Sungai Raya',
                'password_hash' => Hash::make('password'),
                'institution' => 'Puskesmas Sungai Raya',
            ]
        );
    }
}
