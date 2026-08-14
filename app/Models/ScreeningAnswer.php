<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreeningAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'screening_session_id',
        'screening_question_id',
        'answer',
        'answered_at',
        'used_text_to_speech',
        'is_superseded',
    ];

    protected function casts(): array
    {
        return [
            'answered_at' => 'datetime',
            'used_text_to_speech' => 'boolean',
            'is_superseded' => 'boolean',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ScreeningSession::class, 'screening_session_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ScreeningQuestion::class, 'screening_question_id');
    }
}
