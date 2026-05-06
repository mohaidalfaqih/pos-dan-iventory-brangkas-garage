<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationCode extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'code',
        'new_email',
        'used',
        'verified',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used'       => 'boolean',
        'verified'   => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isValid(): bool
    {
        return ! $this->used && $this->expires_at->isFuture();
    }
}
