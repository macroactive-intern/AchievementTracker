<?php

namespace Tests\Feature;

use App\Models\PlayerAchievement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AchievementPipelineTest extends TestCase
{
    use RefreshDatabase;

    private User $player;

    protected function setUp(): void
    {
        parent::setUp();
        $this->player = User::factory()->create();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function recordAction(string $actionType, ?string $occurredAt = null): TestResponse
    {
        $payload = ['action_type' => $actionType];

        if ($occurredAt !== null) {
            $payload['occurred_at'] = $occurredAt;
        }

        return $this->actingAs($this->player)->postJson('/api/player-actions', $payload);
    }

    private function achievementCount(string $key): int
    {
        return PlayerAchievement::where('player_id', $this->player->id)
            ->where('achievement_key', $key)
            ->count();
    }

    // -------------------------------------------------------------------------
    // Tests
    // -------------------------------------------------------------------------

    // 24 — full pipeline
    public function test_posting_match_won_creates_action_and_unlocks_first_win(): void
    {
        $this->recordAction('match_won')->assertStatus(201);

        $this->assertDatabaseHas('player_actions', [
            'player_id'   => $this->player->id,
            'action_type' => 'match_won',
        ]);

        $this->assertDatabaseHas('player_achievements', [
            'player_id'       => $this->player->id,
            'achievement_key' => 'first_win',
        ]);
    }

    // 25 — no double-awarding
    public function test_first_win_is_awarded_exactly_once_regardless_of_subsequent_actions(): void
    {
        $this->recordAction('match_won');
        $this->recordAction('match_won');

        $this->assertSame(1, $this->achievementCount('first_win'));
    }

    // 26 — ten_wins
    public function test_ten_match_won_actions_unlock_both_first_win_and_ten_wins_without_duplicates(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->recordAction('match_won');
        }

        $this->assertSame(1, $this->achievementCount('first_win'));
        $this->assertSame(1, $this->achievementCount('ten_wins'));
    }

    // 27 — week_warrior positive
    public function test_seven_logins_within_seven_days_unlocks_week_warrior(): void
    {
        $base = '2026-01-01';

        for ($day = 0; $day < 7; $day++) {
            $this->recordAction('login', date('Y-m-d', strtotime("{$base} +{$day} days")));
        }

        $this->assertSame(1, $this->achievementCount('week_warrior'));
    }

    // 27 — week_warrior negative
    public function test_seven_logins_spread_outside_seven_day_window_do_not_unlock_week_warrior(): void
    {
        $base = '2026-01-01';

        for ($i = 0; $i < 7; $i++) {
            $this->recordAction('login', date('Y-m-d', strtotime("{$base} +{$i} months")));
        }

        $this->assertSame(0, $this->achievementCount('week_warrior'));
    }
}
