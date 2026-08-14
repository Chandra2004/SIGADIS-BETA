<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScreeningQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'question_text',
        'category',
        'applies_to_session_type',
        'is_critical_symptom',
        'rule_reviewed_by',
        'rule_reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'applies_to_session_type' => 'array',
            'is_critical_symptom' => 'boolean',
            'rule_reviewed_at' => 'datetime',
        ];
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ScreeningAnswer::class);
    }

    #[Scope]
    protected function forSessionType(Builder $query, string $sessionType): Builder
    {
        return $query->whereJsonContains('applies_to_session_type', $sessionType);
    }

    #[Scope]
    protected function critical(Builder $query): Builder
    {
        return $query->where('is_critical_symptom', true);
    }
}
