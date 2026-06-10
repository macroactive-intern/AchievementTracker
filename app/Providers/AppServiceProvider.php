<?php

namespace App\Providers;

use App\Models\PlayerAction;
use App\Observers\PlayerActionObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        PlayerAction::observe(PlayerActionObserver::class);
    }
}
