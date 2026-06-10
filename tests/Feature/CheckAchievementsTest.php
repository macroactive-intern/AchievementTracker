<?php

namespace Tests\Feature;

use App\Events\AchievementUnlocked;
use App\Events\PlayerActionRecorded;
use App\Listeners\CheckAchievements;
use App\Models\PlayerAction;
use App\Models\User;
use App\Services\AchievementService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CheckAchievementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_achievements_dispatches_achievement_unlocked_for_first_win(): void
    {
        Event::fake([AchievementUnlocked::class]);

        $player = User::factory()->create();

        $action = PlayerAction::create([
            'player_id'   => $player->id,
            'action_type' => 'match_won',
            'occurred_at' => Carbon::now(),
        ]);

        $listener = new CheckAchievements(new AchievementService());
        $listener->handle(new PlayerActionRecorded($action));

        Event::assertDispatched(
            AchievementUnlocked::class,
            fn (AchievementUnlocked $event) =>
                $event->playerId === $player->id &&
                $event->achievementKey === 'first_win',
        );
    }
}
