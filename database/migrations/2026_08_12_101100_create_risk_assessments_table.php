<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pregnancy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('screening_session_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('risk_level', ['rendah', 'sedang', 'tinggi']);
            $table->json('triggered_rule_codes');
            $table->boolean('is_data_incomplete')->default(false);
            $table->text('recommendation_text');
            $table->boolean('disclaimer_shown')->default(true);
            $table->timestamp('assessed_at');
            $table->timestamps();

            $table->index(['pregnancy_id', 'assessed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_assessments');
    }
};
