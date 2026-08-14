<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pregnancy_id')->constrained()->cascadeOnDelete();
            $table->enum('trigger_type', ['auto_risk_high', 'manual_button']);
            $table->foreignId('risk_assessment_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['pending', 'delivered', 'being_handled', 'resolved'])->default('pending');
            $table->timestamp('triggered_at');
            $table->foreignId('handled_by_id')->nullable()->constrained('healthcare_workers')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamp('escalated_to_kader_at')->nullable();
            $table->timestamps();

            $table->index('pregnancy_id');
            $table->index('status');
            $table->index('triggered_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_alerts');
    }
};
