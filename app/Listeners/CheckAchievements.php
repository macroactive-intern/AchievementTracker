<?php

namespace App\Listeners;

use App\Events\AchievementUnlocked;
use App\Events\PlayerActionRecorded;
use App\Services\AchievementService;

class CheckAchievements
{
    public function __construct(private AchievementService $achievements) {}

    public function handle(PlayerActionRecorded $event): void
    {
        $player = $event->playerAction->player;
        $newKeys = $this->achievements->newlyEarnedAchievementKeysForPlayer($player);

        foreach ($newKeys as $key) {
            AchievementUnlocked::dispatch(
                playerId: $player->id,
                achievementKey: $key,
                unlockedAt: $event->playerAction->occurred_at,
            );
        }
    }
}
