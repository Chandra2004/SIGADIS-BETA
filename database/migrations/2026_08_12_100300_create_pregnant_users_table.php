<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pregnant_users', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number', 20)->unique();
            $table->string('full_name');
            $table->string('profile_photo_path')->nullable();
            $table->string('password_hash');
            $table->timestamp('otp_verified_at')->nullable();
            $table->enum('text_size', ['normal', 'besar'])->default('normal');
            $table->boolean('tts_enabled')->default(true);
            $table->boolean('screening_reminder_enabled')->default(true);
            $table->boolean('gps_permission_enabled')->default(false);
            $table->boolean('share_data_with_midwife_enabled')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pregnant_users');
    }
};
