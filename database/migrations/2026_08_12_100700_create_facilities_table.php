<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['puskesmas', 'pustu', 'polindes', 'rumah_sakit', 'klinik']);
            $table->string('hospital_class', 5)->nullable();
            $table->boolean('has_icu')->default(false);
            $table->boolean('has_nicu')->default(false);
            $table->unsignedSmallInteger('nicu_bed_count')->nullable();
            $table->enum('ambulance_status', ['siaga', 'dalam_perjalanan', 'tidak_tersedia'])->nullable();
            $table->string('region_code', 20);
            $table->text('address');
            $table->string('phone_number', 20)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();

            $table->index('region_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
