<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Catatan kondisi ibu/bayi saat bidan menandai persalinan (Flows.md §13.3). */
    public function up(): void
    {
        Schema::table('pregnancies', function (Blueprint $table) {
            $table->text('delivery_notes')->nullable()->after('nifas_marked_at');
        });
    }

    public function down(): void
    {
        Schema::table('pregnancies', function (Blueprint $table) {
            $table->dropColumn('delivery_notes');
        });
    }
};
