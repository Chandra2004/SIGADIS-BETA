<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Final Midwife Assessment" (Case Closed Final Confirmation Modal,
     * desain Figma) — diisi bidan sekali, bersamaan dengan Konfirmasi Case
     * Closed (Flows.md §15). Satu baris per kasus ditutup, bukan per pregnancy
     * murni supaya kalau nanti ada alur re-open (di luar cakupan sekarang)
     * riwayat assessment lama tetap ada.
     */
    public function up(): void
    {
        Schema::create('postpartum_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pregnancy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('midwife_id')->constrained('healthcare_workers')->cascadeOnDelete();
            $table->enum('physical_recovery_status', ['complete', 'needs_followup']);
            $table->enum('infant_growth_status', ['on_target', 'needs_monitoring']);
            $table->decimal('infant_weight_kg', 4, 2)->nullable();
            $table->enum('family_planning_status', ['counseled_decided', 'counseled_undecided', 'not_counseled']);
            $table->string('family_planning_method')->nullable();
            $table->text('next_steps')->nullable();
            $table->text('final_summary_note')->nullable();
            $table->timestamp('confirmed_at');
            $table->timestamps();

            $table->index('pregnancy_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postpartum_assessments');
    }
};
