<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * nifas_started_at ibu is a medical event date the bidan can backdate;
     * the 24-hour correction window (Flows.md §13.5) must anchor to when
     * the bidan actually pressed the button, so it needs its own column.
     */
    public function up(): void
    {
        Schema::table('pregnancies', function (Blueprint $table) {
            $table->timestamp('nifas_marked_at')->nullable()->after('nifas_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('pregnancies', function (Blueprint $table) {
            $table->dropColumn('nifas_marked_at');
        });
    }
};
