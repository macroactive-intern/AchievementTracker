<?php

return [

    'first_win' => [
        'type'  => 'match_won',
        'count' => 1,
    ],

    'ten_wins' => [
        'type'  => 'match_won',
        'count' => 10,
    ],

    'kill_streak' => [
        'type'  => 'kill_streak',
        'count' => 5,
    ],

    'week_warrior' => [
        'type'        => 'login',
        'count'       => 7,
        'window_days' => 7,
    ],

];
