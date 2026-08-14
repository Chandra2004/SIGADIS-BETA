<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Buat filter & info di modal rujukan (Process Referral / Pilih Faskes Modal). */
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->string('hospital_class', 5)->nullable()->after('type');
            $table->boolean('has_icu')->default(false)->after('hospital_class');
            $table->boolean('has_nicu')->default(false)->after('has_icu');
            $table->unsignedSmallInteger('nicu_bed_count')->nullable()->after('has_nicu');
            $table->enum('ambulance_status', ['siaga', 'dalam_perjalanan', 'tidak_tersedia'])->nullable()->after('nicu_bed_count');
        });
    }

    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropColumn(['hospital_class', 'has_icu', 'has_nicu', 'nicu_bed_count', 'ambulance_status']);
        });
    }
};
