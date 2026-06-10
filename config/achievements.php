<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Achievement Definitions
    |--------------------------------------------------------------------------
    |
    | This is the source of truth for all achievement rules. Each key maps to
    | an achievement that is evaluated by replaying the player_actions log.
    |
    | Structure per entry:
    |   'key'         => unique string identifier, matches player_achievements.achievement_key
    |   'name'        => human-readable label
    |   'description' => shown to the player
    |   'action_type' => which player_actions.action_type triggers evaluation
    |   'threshold'   => how many qualifying actions are required to unlock
    |
    */

    'definitions' => [

        'first_win' => [
            'name'        => 'First Win',
            'description' => 'Win your first match.',
            'action_type' => 'match_won',
            'threshold'   => 1,
        ],

        'win_streak_5' => [
            'name'        => 'On a Roll',
            'description' => 'Win 5 matches.',
            'action_type' => 'match_won',
            'threshold'   => 5,
        ],

        'kill_streak_10' => [
            'name'        => 'Unstoppable',
            'description' => 'Record 10 kill streaks.',
            'action_type' => 'kill_streak',
            'threshold'   => 10,
        ],

        'login_7_days' => [
            'name'        => 'Dedicated',
            'description' => 'Log in 7 times.',
            'action_type' => 'login',
            'threshold'   => 7,
        ],

    ],

];
