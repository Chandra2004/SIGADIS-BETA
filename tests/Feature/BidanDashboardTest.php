<?php

namespace Tests\Feature;

use App\Models\HealthcareWorker;
use App\Models\MidwifeAssignment;
use App\Models\PregnantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BidanDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_bidan_can_view_dashboard_with_patient_metrics_and_alerts(): void
    {
        $bidan = HealthcareWorker::factory()->create([
            'role' => 'bidan',
            'status' => 'verified',
            'region_code' => 'DESA_A',
            'is_available' => true,
        ]);

        $user = PregnantUser::factory()->create();
        $pregnancy = $user->pregnancies()->create([
            'mother_name' => 'Ibu Rahmawati',
            'gestational_age_weeks_at_registration' => 28,
            'region_code' => 'DESA_A',
            'status' => 'hamil',
        ]);

        MidwifeAssignment::create([
            'pregnancy_id' => $pregnancy->id,
            'midwife_id' => $bidan->id,
            'assignment_method' => 'auto_zonasi',
            'is_active' => true,
            'started_at' => now(),
        ]);

        $riskAssessment = $pregnancy->riskAssessments()->create([
            'risk_level' => 'tinggi',
            'triggered_rule_codes' => ['bleeding_heavy'],
            'recommendation_text' => 'Segera rujuk ke RS PONEK',
            'assessed_at' => now(),
        ]);

        $alert = $pregnancy->emergencyAlerts()->create([
            'trigger_type' => 'manual_button',
            'risk_assessment_id' => $riskAssessment->id,
            'status' => 'pending',
            'triggered_at' => now(),
        ]);

        $response = $this->actingAs($bidan, 'staff')->get(route('bidan.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Desktop/Dashboard')
            ->where('summary.total', 1)
            ->where('summary.risiko_tinggi', 1)
            ->has('patients', 1)
            ->has('pendingAlerts', 1)
            ->where('pendingAlerts.0.mother_name', 'Ibu Rahmawati')
        );
    }

    public function test_bidan_can_filter_patients_by_risk(): void
    {
        $bidan = HealthcareWorker::factory()->create([
            'role' => 'bidan',
            'status' => 'verified',
            'region_code' => 'DESA_A',
        ]);

        $user1 = PregnantUser::factory()->create();
        $pregnancy1 = $user1->pregnancies()->create([
            'mother_name' => 'Ibu Tinggi',
            'gestational_age_weeks_at_registration' => 30,
            'region_code' => 'DESA_A',
            'status' => 'hamil',
        ]);
        MidwifeAssignment::create([
            'pregnancy_id' => $pregnancy1->id,
            'midwife_id' => $bidan->id,
            'assignment_method' => 'auto_zonasi',
            'is_active' => true,
            'started_at' => now(),
        ]);
        $pregnancy1->riskAssessments()->create([
            'risk_level' => 'tinggi',
            'triggered_rule_codes' => [],
            'recommendation_text' => '-',
            'assessed_at' => now(),
        ]);

        $response = $this->actingAs($bidan, 'staff')->get(route('bidan.dashboard', ['filter' => 'tinggi']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('patients', 1)
            ->where('patients.0.mother_name', 'Ibu Tinggi')
        );
    }

    public function test_bidan_can_toggle_availability_duty_status(): void
    {
        $bidan = HealthcareWorker::factory()->create([
            'role' => 'bidan',
            'status' => 'verified',
            'is_available' => true,
        ]);

        // 1. Deactivate (Set to Leave / Unavailable)
        $response = $this->actingAs($bidan, 'staff')->post(route('bidan.availability.deactivate'), [
            'unavailable_from' => now()->toDateString(),
            'unavailable_until' => now()->addDays(7)->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertFalse((bool) $bidan->fresh()->is_available);

        // 2. Reactivate (Back to Duty)
        $response2 = $this->actingAs($bidan, 'staff')->post(route('bidan.availability.reactivate'));
        $response2->assertRedirect();
        $this->assertTrue((bool) $bidan->fresh()->is_available);
    }

    public function test_unverified_worker_cannot_access_bidan_dashboard(): void
    {
        $pendingWorker = HealthcareWorker::factory()->create([
            'status' => 'pending',
        ]);

        $response = $this->actingAs($pendingWorker, 'staff')->get(route('bidan.dashboard'));
        $response->assertRedirect(route('auth.staff.pending'));
    }

    public function test_bidan_can_view_alerts_list(): void
    {
        $bidan = HealthcareWorker::factory()->create([
            'role' => 'bidan',
            'status' => 'verified',
            'region_code' => 'DESA_A',
        ]);

        $response = $this->actingAs($bidan, 'staff')->get(route('bidan.alerts.index'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Desktop/AlertList'));
    }

    public function test_bidan_can_view_facilities_catalog(): void
    {
        $bidan = HealthcareWorker::factory()->create([
            'role' => 'bidan',
            'status' => 'verified',
            'region_code' => 'DESA_A',
        ]);

        $response = $this->actingAs($bidan, 'staff')->get(route('bidan.referrals.index'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Desktop/FacilityCatalog'));
    }

    public function test_bidan_can_view_cuti_page(): void
    {
        $bidan = HealthcareWorker::factory()->create([
            'role' => 'bidan',
            'status' => 'verified',
            'region_code' => 'DESA_A',
        ]);

        $response = $this->actingAs($bidan, 'staff')->get(route('bidan.availability.index'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Desktop/Cuti'));
    }

    public function test_bidan_can_view_notifications_page(): void
    {
        $bidan = HealthcareWorker::factory()->create([
            'role' => 'bidan',
            'status' => 'verified',
            'region_code' => 'DESA_A',
        ]);

        $response = $this->actingAs($bidan, 'staff')->get(route('bidan.notifications.index'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Desktop/Notifikasi'));
    }

    public function test_bidan_can_view_profile_and_update_password(): void
    {
        $bidan = HealthcareWorker::factory()->create([
            'role' => 'bidan',
            'status' => 'verified',
            'region_code' => 'DESA_A',
            'password_hash' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($bidan, 'staff')->get(route('bidan.profile.index'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Desktop/Profile'));

        $updateResponse = $this->actingAs($bidan, 'staff')->post(route('bidan.profile.update-password'), [
            'current_password' => 'password123',
            'password' => 'newpassword321',
            'password_confirmation' => 'newpassword321',
        ]);

        $updateResponse->assertRedirect();
        $this->assertTrue(Hash::check('newpassword321', $bidan->fresh()->password_hash));
    }
}
