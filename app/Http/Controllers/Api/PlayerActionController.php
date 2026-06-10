<?php

namespace App\Http\Controllers\Api;

use App\Events\PlayerActionRecorded;
use App\Http\Controllers\Controller;
use App\Models\PlayerAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PlayerActionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'action_type' => ['required', 'string'],
            'payload'     => ['nullable', 'array'],
            'occurred_at' => ['nullable', 'date'],
        ]);

        $action = PlayerAction::create([
            'player_id'   => $request->user()->id,
            'action_type' => $data['action_type'],
            'payload'     => $data['payload'] ?? null,
            'occurred_at' => isset($data['occurred_at'])
                ? Carbon::parse($data['occurred_at'])
                : now(),
        ]);

        PlayerActionRecorded::dispatch($action);

        return response()->json($action, 201);
    }
}
