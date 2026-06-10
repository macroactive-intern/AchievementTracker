<?php

namespace App\Events;

use Carbon\Carbon;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AchievementUnlocked
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $playerId,
        public readonly string $achievementKey,
        public readonly Carbon $unlockedAt,
    ) {}
}
