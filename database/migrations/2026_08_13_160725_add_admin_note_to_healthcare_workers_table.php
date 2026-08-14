<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /** Flows.md §26.3.2: catatan admin, opsional saat verifikasi, wajib saat menolak. */
    public function up(): void
    {
        Schema::table('healthcare_workers', function (Blueprint $table) {
            $table->text('admin_note')->nullable()->after('verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('healthcare_workers', function (Blueprint $table) {
            $table->dropColumn('admin_note');
        });
    }
};
