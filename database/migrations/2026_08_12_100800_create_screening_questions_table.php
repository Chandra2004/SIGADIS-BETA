<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_questions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->text('question_text');
            $table->enum('category', [
                'perdarahan', 'preeklamsia', 'infeksi', 'gerakan_janin',
                'nyeri_perut', 'kejang', 'nifas_lain',
            ]);
            // MySQL SET diganti JSON: array session_type ["initial","periodic"] — portable ke SQLite juga
            $table->json('applies_to_session_type');
            $table->boolean('is_critical_symptom')->default(false);
            $table->string('rule_reviewed_by')->nullable();
            $table->timestamp('rule_reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_questions');
    }
};
