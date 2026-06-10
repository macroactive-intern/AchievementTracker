<?php

namespace App\Events;

use App\Models\PlayerAction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerActionRecorded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly PlayerAction $playerAction,
    ) {}
}
