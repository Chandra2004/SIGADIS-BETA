<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catatan kunjungan klinis bidan (Patient Screening Timeline & History
     * View, desain Figma) — beda dari screening_sessions (yang diisi ibu
     * hamil sendiri): ini catatan pemeriksaan tatap muka oleh bidan, boleh
     * berdiri sendiri tanpa sesi skrining terkait.
     */
    public function up(): void
    {
        Schema::create('clinical_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pregnancy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('midwife_id')->constrained('healthcare_workers')->cascadeOnDelete();
            $table->enum('visit_type', ['routine_screening', 'follow_up', 'other'])->default('routine_screening');
            $table->enum('status_tag', ['normal', 'monitor', 'elevated'])->default('normal');
            $table->unsignedSmallInteger('blood_pressure_systolic')->nullable();
            $table->unsignedSmallInteger('blood_pressure_diastolic')->nullable();
            $table->json('symptoms')->nullable();
            $table->text('clinical_notes')->nullable();
            $table->timestamp('visited_at');
            $table->timestamps();

            $table->index(['pregnancy_id', 'visited_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_visits');
    }
};
