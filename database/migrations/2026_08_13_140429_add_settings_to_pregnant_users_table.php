<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pengaturan Aplikasi + toggle Privasi & Data Saya tambahan (desain
     * Figma). Sengaja TANPA pengaturan bahasa — selalu Bahasa Indonesia.
     */
    public function up(): void
    {
        Schema::table('pregnant_users', function (Blueprint $table) {
            $table->enum('text_size', ['normal', 'besar'])->default('normal');
            $table->boolean('tts_enabled')->default(true);
            $table->boolean('screening_reminder_enabled')->default(true);
            $table->boolean('education_updates_enabled')->default(true);
            $table->boolean('gps_permission_enabled')->default(false);
            $table->boolean('share_data_with_midwife_enabled')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('pregnant_users', function (Blueprint $table) {
            $table->dropColumn([
                'text_size',
                'tts_enabled',
                'screening_reminder_enabled',
                'education_updates_enabled',
                'gps_permission_enabled',
                'share_data_with_midwife_enabled',
            ]);
        });
    }
};
