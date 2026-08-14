<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('midwife_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pregnancy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('midwife_id')->constrained('healthcare_workers')->cascadeOnDelete();
            $table->enum('assignment_method', ['auto_zonasi', 'manual_pilih']);
            $table->boolean('is_active')->default(true);
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['pregnancy_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('midwife_assignments');
    }
};
