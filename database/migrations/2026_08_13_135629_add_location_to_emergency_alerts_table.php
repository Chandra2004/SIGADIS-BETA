<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lokasi GPS ibu hamil saat alert dipicu (dialog "Aktivasi Sinyal Darurat
     * SOS", Design/Flows) — snapshot di alert, bukan di pregnancy, karena
     * lokasi berubah tiap kejadian, bukan atribut tetap kehamilan.
     */
    public function up(): void
    {
        Schema::table('emergency_alerts', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('triggered_at');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('emergency_alerts', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
