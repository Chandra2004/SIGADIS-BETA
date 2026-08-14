<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('healthcare_worker_id')->constrained()->cascadeOnDelete();
            $table->string('fcm_token');
            $table->enum('platform', ['desktop_browser', 'mobile']);
            $table->timestamp('registered_at');
            $table->timestamp('last_seen_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('healthcare_worker_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
