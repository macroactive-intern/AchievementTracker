<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class PlayerAction extends Model
{
    protected $fillable = [
        'player_id',
        'action_type',
        'payload',
        'occurred_at',
    ];

    protected $casts = [
        'payload'     => 'array',
        'occurred_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('PlayerAction records are append-only and cannot be updated.'));
        static::deleting(fn () => throw new LogicException('PlayerAction records are append-only and cannot be deleted.'));
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_id');
    }
}
