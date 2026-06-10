<?php

namespace App\Listeners;

use App\Events\AchievementUnlocked;
use App\Models\PlayerAchievement;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class NotifyPlayerOfAchievement implements ShouldQueue
{
    public function handle(AchievementUnlocked $event): void
    {
        PlayerAchievement::firstOrCreate(
            [
                'player_id'       => $event->playerId,
                'achievement_key' => $event->achievementKey,
            ],
            [
                'unlocked_at' => $event->unlockedAt,
            ],
        );

        $player = User::findOrFail($event->playerId);

        Log::info('Achievement unlocked', [
            'player_id'       => $player->id,
            'email'           => $player->email,
            'achievement_key' => $event->achievementKey,
        ]);
    }
}
