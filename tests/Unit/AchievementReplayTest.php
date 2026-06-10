<?php

namespace Tests\Unit;

use App\Models\PlayerAction;
use App\Models\PlayerAchievement;
use App\Models\User;
use App\Services\AchievementService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AchievementReplayTest extends TestCase
{
    use RefreshDatabase;

    // 28 — replay produces the same achievement set as incremental processing
    public function test_replay_matches_incremental_pipeline_for_same_action_history(): void
    {
        // Player A: actions recorded through the real event pipeline.
        $playerA = User::factory()->create();

        $this->actingAs($playerA)->postJson('/api/player-actions', ['action_type' => 'match_won']);

        $keysA = PlayerAchievement::where('player_id', $playerA->id)
            ->pluck('achievement_key')
            ->sort()
            ->values()
            ->all();

        // Player B: same action history written directly to the DB — no events fired.
        $playerB = User::factory()->create();

        PlayerAction::create([
            'player_id'   => $playerB->id,
            'action_type' => 'match_won',
            'occurred_at' => Carbon::now(),
        ]);

        $keysB = (new AchievementService())->replayForPlayer($playerB);
        sort($keysB);

        $this->assertSame($keysA, $keysB);
    }

    // 29 — replayForPlayer() retroactively grants missing achievements
    public function test_replay_creates_missing_achievements_for_player_with_existing_actions(): void
    {
        $player = User::factory()->create();

        // Actions exist but no achievements have been awarded yet.
        PlayerAction::create([
            'player_id'   => $player->id,
            'action_type' => 'match_won',
            'occurred_at' => Carbon::now(),
        ]);

        $this->assertDatabaseMissing('player_achievements', [
            'player_id'       => $player->id,
            'achievement_key' => 'first_win',
        ]);

        (new AchievementService())->replayForPlayer($player);

        $this->assertDatabaseHas('player_achievements', [
            'player_id'       => $player->id,
            'achievement_key' => 'first_win',
        ]);
    }
}
