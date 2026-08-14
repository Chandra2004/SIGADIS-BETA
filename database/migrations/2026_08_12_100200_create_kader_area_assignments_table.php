<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kader_area_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kader_id')->constrained('healthcare_workers')->cascadeOnDelete();
            $table->string('region_code', 20);
            $table->enum('kader_priority', ['primary', 'secondary']);
            $table->timestamps();

            $table->index('region_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kader_area_assignments');
    }
};
