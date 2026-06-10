<?php

namespace App\Console\Commands;

use App\Models\PlayerAchievement;
use App\Models\User;
use App\Services\AchievementService;
use Illuminate\Console\Command;

class ReplayAchievements extends Command
{
    protected $signature = 'achievements:replay {--player= : Only replay for the given player ID}';

    protected $description = 'Replay the player action log and grant any missing achievements';

    public function handle(AchievementService $achievements): int
    {
        $query = User::query();

        if ($playerId = $this->option('player')) {
            $query->whereKey($playerId);
        }

        $players = $query->get();

        if ($players->isEmpty()) {
            $this->warn('No players found.');
            return self::SUCCESS;
        }

        $totalCreated = 0;

        foreach ($players as $player) {
            $before = PlayerAchievement::where('player_id', $player->id)->count();

            $achievements->replayForPlayer($player);

            $created = PlayerAchievement::where('player_id', $player->id)->count() - $before;

            if ($created > 0) {
                $this->line("  Player {$player->id}: {$created} achievement(s) created.");
            }

            $totalCreated += $created;
        }

        $this->info("Done. {$totalCreated} achievement(s) created across {$players->count()} player(s).");

        return self::SUCCESS;
    }
}
