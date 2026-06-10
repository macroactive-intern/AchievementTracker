<?php

namespace App\Services;

use App\Models\PlayerAction;
use App\Models\PlayerAchievement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AchievementService
{
    /**
     * Returns all achievement keys the player currently qualifies for,
     * calculated by replaying the full player_actions log.
     */
    public function earnedAchievementKeysForPlayer(User $player): array
    {
        return array_keys($this->resolveEarned($player));
    }

    /**
     * Returns only the keys the player has earned but not yet been awarded.
     * Safe to call repeatedly — will never double-award.
     */
    public function newlyEarnedAchievementKeysForPlayer(User $player): array
    {
        $earned = $this->earnedAchievementKeysForPlayer($player);

        $existing = PlayerAchievement::where('player_id', $player->id)
            ->pluck('achievement_key')
            ->all();

        return array_values(array_diff($earned, $existing));
    }

    /**
     * Replays the full event log and creates any missing PlayerAchievement
     * records. Does not touch PlayerAction rows. Idempotent.
     *
     * @return string[] Final set of earned achievement keys.
     */
    public function replayForPlayer(User $player): array
    {
        $earned = $this->resolveEarned($player);

        $existing = PlayerAchievement::where('player_id', $player->id)
            ->pluck('achievement_key')
            ->all();

        foreach ($earned as $key => $unlockedAt) {
            if (in_array($key, $existing, strict: true)) {
                continue;
            }

            PlayerAchievement::create([
                'player_id'       => $player->id,
                'achievement_key' => $key,
                'unlocked_at'     => $unlockedAt,
            ]);
        }

        return array_keys($earned);
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    /**
     * Core replay: loads the action log, evaluates every rule in config,
     * and returns [achievementKey => Carbon $unlockedAt] for all earned ones.
     *
     * @return array<string, Carbon>
     */
    private function resolveEarned(User $player): array
    {
        $rules = config('achievements', []);

        $actions = PlayerAction::where('player_id', $player->id)
            ->orderBy('occurred_at')
            ->get();

        $earned = [];

        foreach ($rules as $key => $rule) {
            $matching = $actions->where('action_type', $rule['type'])->values();

            $unlockedAt = isset($rule['window_days'])
                ? $this->evaluateWindowedRule($matching, $rule)
                : $this->evaluateCountRule($matching, $rule);

            if ($unlockedAt !== null) {
                $earned[$key] = $unlockedAt;
            }
        }

        return $earned;
    }

    /**
     * Count-based rule: qualifies if total matching actions >= required count.
     * unlocked_at is the occurred_at of the threshold-completing action.
     */
    private function evaluateCountRule(Collection $matching, array $rule): ?Carbon
    {
        if ($matching->count() >= $rule['count']) {
            return $matching->get($rule['count'] - 1)->occurred_at;
        }

        return null;
    }

    /**
     * Windowed rule: qualifies if any sliding window of window_days contains
     * >= required count of matching actions. Each action is treated as a
     * potential window start, so non-consecutive logins can still qualify.
     *
     * unlocked_at is the occurred_at of the last action inside the first
     * qualifying window.
     */
    private function evaluateWindowedRule(Collection $matching, array $rule): ?Carbon
    {
        $required    = $rule['count'];
        $windowDays  = $rule['window_days'];

        foreach ($matching as $anchor) {
            $windowEnd = $anchor->occurred_at->copy()->addDays($windowDays);

            $inWindow = $matching->filter(
                fn (PlayerAction $a) => $a->occurred_at->between($anchor->occurred_at, $windowEnd)
            );

            if ($inWindow->count() >= $required) {
                return $inWindow->last()->occurred_at;
            }
        }

        return null;
    }
}
