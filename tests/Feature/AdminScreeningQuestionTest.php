<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\ScreeningQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminScreeningQuestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_screening_bank_with_metrics(): void
    {
        $admin = AdminUser::factory()->create();

        ScreeningQuestion::create([
            'code' => 'bleeding_heavy',
            'question_text' => 'Apakah Ibu mengalami perdarahan banyak dari jalan lahir?',
            'category' => 'perdarahan',
            'applies_to_session_type' => ['initial', 'periodic'],
            'is_critical_symptom' => true,
            'rule_reviewed_by' => 'dr. Budi, Sp.OG',
            'rule_reviewed_at' => now(),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.bank-soal.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/BankSoal')
            ->has('questions', 1)
            ->where('metrics.total', 1)
            ->where('metrics.critical', 1)
            ->where('metrics.initial', 1)
            ->where('metrics.reviewed', 1)
        );
    }

    public function test_admin_can_create_a_new_question(): void
    {
        $admin = AdminUser::factory()->create();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.bank-soal.store'), [
            'code' => 'headache_severe_new',
            'question_text' => 'Apakah Ibu sakit kepala hebat tak tertahankan?',
            'category' => 'preeklamsia',
            'applies_to_session_type' => ['initial', 'periodic'],
            'is_critical_symptom' => true,
            'rule_reviewed_by' => 'dr. Anita, Sp.OG',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('screening_questions', [
            'code' => 'headache_severe_new',
            'category' => 'preeklamsia',
            'is_critical_symptom' => 1,
            'rule_reviewed_by' => 'dr. Anita, Sp.OG',
        ]);
    }

    public function test_admin_can_update_a_question(): void
    {
        $admin = AdminUser::factory()->create();

        $question = ScreeningQuestion::create([
            'code' => 'fever_high_temp',
            'question_text' => 'Apakah Ibu demam tinggi?',
            'category' => 'infeksi',
            'applies_to_session_type' => ['initial'],
            'is_critical_symptom' => false,
        ]);

        $response = $this->actingAs($admin, 'admin')->put(route('admin.bank-soal.update', $question->id), [
            'code' => 'fever_high_temp',
            'question_text' => 'Apakah Ibu mengalami demam tinggi lebih dari 38 derajat?',
            'category' => 'infeksi',
            'applies_to_session_type' => ['initial', 'periodic'],
            'is_critical_symptom' => true,
            'rule_reviewed_by' => 'dr. Budi, Sp.OG',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('screening_questions', [
            'id' => $question->id,
            'question_text' => 'Apakah Ibu mengalami demam tinggi lebih dari 38 derajat?',
            'is_critical_symptom' => 1,
            'rule_reviewed_by' => 'dr. Budi, Sp.OG',
        ]);
    }

    public function test_admin_can_record_medical_governance_review(): void
    {
        $admin = AdminUser::factory()->create();

        $question = ScreeningQuestion::create([
            'code' => 'seizure_alert',
            'question_text' => 'Apakah Ibu pernah kejang?',
            'category' => 'kejang',
            'applies_to_session_type' => ['initial', 'periodic'],
            'is_critical_symptom' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.bank-soal.review', $question->id), [
            'reviewer_name' => 'Tim Ahli POGI Kalbar',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('screening_questions', [
            'id' => $question->id,
            'rule_reviewed_by' => 'Tim Ahli POGI Kalbar',
        ]);
    }

    public function test_admin_can_delete_a_question(): void
    {
        $admin = AdminUser::factory()->create();

        $question = ScreeningQuestion::create([
            'code' => 'temp_question',
            'question_text' => 'Pertanyaan uji coba?',
            'category' => 'nifas_lain',
            'applies_to_session_type' => ['nifas'],
            'is_critical_symptom' => false,
        ]);

        $response = $this->actingAs($admin, 'admin')->delete(route('admin.bank-soal.destroy', $question->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('screening_questions', ['id' => $question->id]);
    }

    public function test_unauthenticated_user_cannot_manage_bank_soal(): void
    {
        $response = $this->get(route('admin.bank-soal.index'));
        $response->assertRedirect(route('auth.admin.login.show'));
    }
}
