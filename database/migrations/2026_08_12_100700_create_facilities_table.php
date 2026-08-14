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
