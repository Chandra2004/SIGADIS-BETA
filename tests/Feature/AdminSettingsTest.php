<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_settings_page(): void
    {
        $admin = AdminUser::factory()->create(['full_name' => 'Admin Utama', 'institution' => 'Puskesmas Sungai Raya']);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.pengaturan.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Pengaturan')
            ->has('admins', 1)
            ->where('currentAdmin.full_name', 'Admin Utama')
            ->where('systemSettings.emergency_timeout_minutes', 3)
        );
    }

    public function test_admin_can_create_a_new_admin_user(): void
    {
        $admin = AdminUser::factory()->create();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.pengaturan.admin.store'), [
            'full_name' => 'dr. Budi Setiawan',
            'email' => 'budi.setiawan@dinkes.go.id',
            'password' => 'password123',
            'institution' => 'Dinas Kesehatan Kab. Kubu Raya',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('admin_users', [
            'full_name' => 'dr. Budi Setiawan',
            'email' => 'budi.setiawan@dinkes.go.id',
            'institution' => 'Dinas Kesehatan Kab. Kubu Raya',
        ]);
    }

    public function test_admin_can_update_profile_and_institution(): void
    {
        $admin = AdminUser::factory()->create(['full_name' => 'Admin Lama', 'institution' => 'Puskesmas Lama']);

        $response = $this->actingAs($admin, 'admin')->put(route('admin.pengaturan.profile.update'), [
            'full_name' => 'Admin Baru',
            'institution' => 'Puskesmas Sungai Raya Pusat',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('admin_users', [
            'id' => $admin->id,
            'full_name' => 'Admin Baru',
            'institution' => 'Puskesmas Sungai Raya Pusat',
        ]);
    }

    public function test_admin_can_delete_another_admin(): void
    {
        $admin1 = AdminUser::factory()->create();
        $admin2 = AdminUser::factory()->create(['email' => 'admin2@sigadis.test']);

        $response = $this->actingAs($admin1, 'admin')->delete(route('admin.pengaturan.admin.destroy', $admin2->id));

        $response->assertRedirect();
        $this->assertSoftDeleted($admin2);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = AdminUser::factory()->create();

        $response = $this->actingAs($admin, 'admin')->delete(route('admin.pengaturan.admin.destroy', $admin->id));

        $response->assertStatus(422);
        $this->assertDatabaseHas('admin_users', ['id' => $admin->id]);
    }
}
