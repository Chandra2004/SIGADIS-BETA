<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emergency_alert_id')->constrained()->cascadeOnDelete();
            $table->foreignId('healthcare_worker_id')->constrained()->cascadeOnDelete();
            $table->enum('recipient_role_at_time', ['bidan_utama', 'kader_primary', 'kader_secondary', 'admin_fallback']);
            $table->enum('delivery_status', ['pending', 'sent', 'failed', 'retrying'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('retry_count')->default(0);
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->index('emergency_alert_id');
            $table->index('delivery_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_recipients');
    }
};
