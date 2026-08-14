<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pregnancy_id')->constrained()->cascadeOnDelete();
            $table->enum('session_type', ['initial', 'periodic', 'nifas']);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_complete')->default(false);
            $table->timestamps();

            $table->index('pregnancy_id');
            $table->index('session_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_sessions');
    }
};
