<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /** PRD.md baris 16: "Bukan ... aplikasi edukasi kesehatan umum" — kolom ini tidak punya alur di Flows.md, dihapus daripada dibiarkan nganggur. */
    public function up(): void
    {
        Schema::table('pregnant_users', function (Blueprint $table) {
            $table->dropColumn('education_updates_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('pregnant_users', function (Blueprint $table) {
            $table->boolean('education_updates_enabled')->default(true);
        });
    }
};
