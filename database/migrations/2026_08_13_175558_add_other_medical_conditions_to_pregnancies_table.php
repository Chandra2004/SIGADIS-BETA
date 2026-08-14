<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Kondisi tambahan di luar 4 pertanyaan wajib Flows.md §3.3.1
     * (kembar/caesar/diabetes gestasional/hipertensi kronis, sudah ada
     * kolom sendiri) -- daftar terbuka, belum dipakai RiskAssessmentEngine,
     * murni data konteks buat bidan.
     */
    public function up(): void
    {
        Schema::table('pregnancies', function (Blueprint $table) {
            $table->json('other_medical_conditions')->nullable()->after('has_chronic_hypertension');
            $table->text('medical_notes')->nullable()->after('other_medical_conditions');
        });
    }

    public function down(): void
    {
        Schema::table('pregnancies', function (Blueprint $table) {
            $table->dropColumn(['other_medical_conditions', 'medical_notes']);
        });
    }
};
