<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminOverrideLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'pregnant_user_id',
        'old_phone_number',
        'new_phone_number',
        'reason',
        'performed_at',
    ];

    protected function casts(): array
    {
        return [
            'performed_at' => 'datetime',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_id');
    }

    public function pregnantUser(): BelongsTo
    {
        return $this->belongsTo(PregnantUser::class);
    }
}
