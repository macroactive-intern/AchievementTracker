<?php

namespace App\Observers;

use App\Models\PlayerAction;
use LogicException;

class PlayerActionObserver
{
    public function updating(PlayerAction $action): never
    {
        throw new LogicException('PlayerAction records are append-only and cannot be updated.');
    }

    public function deleting(PlayerAction $action): never
    {
        throw new LogicException('PlayerAction records are append-only and cannot be deleted.');
    }
}
