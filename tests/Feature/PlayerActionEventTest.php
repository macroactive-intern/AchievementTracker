<?php

namespace Tests\Feature;

use App\Events\PlayerActionRecorded;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PlayerActionEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_posting_a_player_action_dispatches_player_action_recorded(): void
    {
        Event::fake();

        $player = User::factory()->create();

        $response = $this->actingAs($player)->postJson('/api/player-actions', [
            'action_type' => 'match_won',
        ]);

        $response->assertStatus(201);

        Event::assertDispatched(
            PlayerActionRecorded::class,
            fn (PlayerActionRecorded $event) =>
                $event->playerAction->player_id === $player->id &&
                $event->playerAction->action_type === 'match_won',
        );
    }
}
