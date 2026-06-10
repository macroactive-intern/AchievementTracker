<?php

use App\Http\Controllers\Api\PlayerActionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::post('player-actions', [PlayerActionController::class, 'store']);
});
