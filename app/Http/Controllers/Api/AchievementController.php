<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlayerAchievement;
use App\Models\User;
use App\Services\AchievementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    public function __construct(private AchievementService $achievements) {}

    public function index(Request $request, int $id): JsonResponse
    {
        $player = User::findOrFail($id);

        $earnedKeys = $this->achievements->earnedAchievementKeysForPlayer($player);

        $timestamps = PlayerAchievement::where('player_id', $player->id)
            ->whereIn('achievement_key', $earnedKeys)
            ->pluck('unlocked_at', 'achievement_key');

        $result = array_map(
            fn (string $key) => [
                'achievement_key' => $key,
                'unlocked_at'     => $timestamps[$key] ?? null,
            ],
            $earnedKeys,
        );

        return response()->json($result);
    }

    public function history(Request $request, int $id): JsonResponse
    {
        $player = User::findOrFail($id);

        $history = PlayerAchievement::where('player_id', $player->id)
            ->orderBy('unlocked_at')
            ->get(['achievement_key', 'unlocked_at']);

        return response()->json($history);
    }
}
