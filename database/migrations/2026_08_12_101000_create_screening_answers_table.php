<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screening_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('screening_question_id')->constrained();
            $table->enum('answer', ['ya', 'tidak']);
            $table->timestamp('answered_at');
            $table->boolean('used_text_to_speech')->default(false);
            // Baris lama saat pengguna mengubah jawaban lewat "Kembali" tetap disimpan
            // (audit trail, Schema.md §4.3) — sengaja TIDAK unique per session+question,
            // supaya bisa ada >1 baris. "hanya satu is_superseded=false per pertanyaan"
            // ditegakkan di service layer, bukan constraint DB (lihat migrations.md skill).
            $table->boolean('is_superseded')->default(false);
            $table->timestamps();

            $table->index(['screening_session_id', 'screening_question_id'], 'screening_answers_session_question_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_answers');
    }
};
