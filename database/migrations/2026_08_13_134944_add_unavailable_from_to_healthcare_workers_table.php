<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Flows.md §34.2.2: rentang cuti, bukan cuma tanggal selesai. */
    public function up(): void
    {
        Schema::table('healthcare_workers', function (Blueprint $table) {
            $table->date('unavailable_from')->nullable()->after('is_available');
        });
    }

    public function down(): void
    {
        Schema::table('healthcare_workers', function (Blueprint $table) {
            $table->dropColumn('unavailable_from');
        });
    }
};
