<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pregnancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pregnant_user_id')->constrained()->cascadeOnDelete();
            $table->string('mother_name');
            $table->date('estimated_due_date')->nullable();
            $table->boolean('hpl_is_estimated')->default(false);
            $table->unsignedInteger('gestational_age_weeks_at_registration');
            $table->boolean('is_twin_pregnancy')->default(false);
            $table->boolean('has_prior_cesarean')->default(false);
            $table->boolean('has_gestational_diabetes')->default(false);
            $table->boolean('has_chronic_hypertension')->default(false);
            $table->json('other_medical_conditions')->nullable();
            $table->text('medical_notes')->nullable();
            $table->string('region_code', 20);
            $table->text('address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->enum('status', ['hamil', 'nifas', 'case_closed'])->default('hamil');
            $table->timestamp('nifas_started_at')->nullable();
            $table->timestamp('nifas_marked_at')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->timestamp('case_closed_at')->nullable();
            $table->foreignId('case_closed_by')->nullable()->constrained('healthcare_workers')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('pregnant_user_id');
            $table->index('region_code');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pregnancies');
    }
};
