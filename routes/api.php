<?php

use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\PlayerActionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::post('/player-actions', [PlayerActionController::class, 'store']);
    Route::get('/players/{id}/achievements', [AchievementController::class, 'index']);
    Route::get('/players/{id}/achievement-history', [AchievementController::class, 'history']);
});
