<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pregnancy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('emergency_alert_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('facility_id')->constrained();
            $table->foreignId('referred_by_id')->constrained('healthcare_workers');
            $table->timestamp('referred_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('pregnancy_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
