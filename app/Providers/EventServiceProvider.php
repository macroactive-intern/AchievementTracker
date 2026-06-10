<?php

namespace App\Providers;

use App\Events\AchievementUnlocked;
use App\Events\PlayerActionRecorded;
use App\Listeners\CheckAchievements;
use App\Listeners\NotifyPlayerOfAchievement;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PlayerActionRecorded::class => [
            CheckAchievements::class,
        ],
        AchievementUnlocked::class => [
            NotifyPlayerOfAchievement::class,
        ],
    ];
}
