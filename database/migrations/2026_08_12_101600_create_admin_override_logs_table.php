<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_override_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admin_users')->cascadeOnDelete();
            $table->foreignId('pregnant_user_id')->constrained()->cascadeOnDelete();
            $table->string('old_phone_number', 20);
            $table->string('new_phone_number', 20);
            $table->text('reason');
            $table->timestamp('performed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_override_logs');
    }
};
