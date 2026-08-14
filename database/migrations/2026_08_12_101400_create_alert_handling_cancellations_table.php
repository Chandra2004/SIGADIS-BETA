<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_handling_cancellations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emergency_alert_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cancelled_handler_id')->constrained('healthcare_workers')->cascadeOnDelete();
            $table->timestamp('cancelled_at');
            $table->timestamps();

            $table->index('emergency_alert_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_handling_cancellations');
    }
};
