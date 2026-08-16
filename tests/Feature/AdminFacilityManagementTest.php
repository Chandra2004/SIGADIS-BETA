<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Facility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFacilityManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_facilities_page_with_metrics(): void
    {
        $admin = AdminUser::factory()->create();

        Facility::create([
            'name' => 'RSUD Sungai Raya PONEK',
            'type' => 'rumah_sakit',
            'region_code' => '33.08.05.2001',
            'address' => 'Jl. Adi Sucipto Km 12',
            'phone_number' => '0561-712345',
            'hospital_class' => 'B',
            'has_icu' => true,
            'has_nicu' => true,
            'nicu_bed_count' => 6,
            'ambulance_status' => 'siaga',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.fasilitas.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Fasilitas')
            ->has('facilities', 1)
            ->where('metrics.total', 1)
            ->where('metrics.hospitals', 1)
            ->where('metrics.nicu_beds', 6)
            ->where('metrics.ambulance_ready', 1)
        );
    }

    public function test_admin_can_create_a_new_facility(): void
    {
        $admin = AdminUser::factory()->create();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.fasilitas.store'), [
            'name' => 'Puskesmas Pembantu Ambangah',
            'type' => 'pustu',
            'region_code' => '33.08.05.2002',
            'address' => 'Jl. Raya Ambangah No. 10',
            'phone_number' => '0561-889900',
            'latitude' => -0.0910,
            'longitude' => 109.3520,
            'has_icu' => false,
            'has_nicu' => false,
            'nicu_bed_count' => 0,
            'ambulance_status' => 'siaga',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('facilities', [
            'name' => 'Puskesmas Pembantu Ambangah',
            'type' => 'pustu',
            'region_code' => '33.08.05.2002',
            'ambulance_status' => 'siaga',
        ]);
    }

    public function test_admin_can_update_an_existing_facility(): void
    {
        $admin = AdminUser::factory()->create();

        $facility = Facility::create([
            'name' => 'Klinik Bersalin Bunda',
            'type' => 'klinik',
            'region_code' => '33.08.05.2003',
            'address' => 'Jl. Protokol No. 5',
            'phone_number' => '08123456789',
            'has_icu' => false,
            'has_nicu' => false,
            'nicu_bed_count' => 0,
            'ambulance_status' => 'siaga',
        ]);

        $response = $this->actingAs($admin, 'admin')->put(route('admin.fasilitas.update', $facility->id), [
            'name' => 'Klinik Bersalin Bunda Utama',
            'type' => 'klinik',
            'region_code' => '33.08.05.2003',
            'address' => 'Jl. Protokol No. 5, Gedung Baru',
            'phone_number' => '08123456789',
            'has_icu' => false,
            'has_nicu' => true,
            'nicu_bed_count' => 2,
            'ambulance_status' => 'dalam_perjalanan',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('facilities', [
            'id' => $facility->id,
            'name' => 'Klinik Bersalin Bunda Utama',
            'has_nicu' => true,
            'nicu_bed_count' => 2,
            'ambulance_status' => 'dalam_perjalanan',
        ]);
    }

    public function test_admin_can_delete_a_facility(): void
    {
        $admin = AdminUser::factory()->create();

        $facility = Facility::create([
            'name' => 'Polindes Desa Lama',
            'type' => 'polindes',
            'region_code' => '33.08.05.2004',
            'address' => 'Dusun 1',
            'ambulance_status' => 'tidak_tersedia',
        ]);

        $response = $this->actingAs($admin, 'admin')->delete(route('admin.fasilitas.destroy', $facility->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('facilities', ['id' => $facility->id]);
    }

    public function test_unauthenticated_user_cannot_manage_facilities(): void
    {
        $response = $this->get(route('admin.fasilitas.index'));
        $response->assertRedirect(route('auth.admin.login.show'));
    }
}
