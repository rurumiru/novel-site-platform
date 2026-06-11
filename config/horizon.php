<?php

use Illuminate\Support\Str;

return [

    'domain' => env('HORIZON_DOMAIN'),
    'path'   => env('HORIZON_PATH', 'horizon'),

    'use' => env('HORIZON_REDIS_CONNECTION', 'horizon'),

    'prefix' => env(
        'HORIZON_PREFIX',
        Str::slug((string) env('APP_NAME', 'laravel'), '_') . '_horizon:'
    ),

    'middleware' => ['web'],

    'waits' => [
        'redis:default' => 60,
        'redis:high'    => 30,
        'redis:low'     => 600,
    ],

    'trim' => [
        'recent'         => 60,
        'pending'        => 60,
        'completed'      => 60,
        'recent_failed'  => 10080,
        'failed'         => 10080,
        'monitored'      => 10080,
    ],

    'metrics' => [
        'trim_snapshots' => [
            'job'   => 24,
            'queue' => 24,
        ],
    ],

    'fast_termination' => false,

    'memory_limit' => 96,

    'defaults' => [
        'supervisor-default' => [
            'connection'    => 'redis',
            'queue'         => ['high', 'default', 'low'],
            'balance'       => 'auto',
            'autoScalingStrategy' => 'time',
            'maxProcesses'  => 6,
            'maxTime'       => 0,
            'maxJobs'       => 0,
            'memory'        => 96,
            'tries'         => 3,
            'timeout'       => 90,
            'nice'          => 0,
            'rest'          => 0,
            'minProcesses'  => 1,
            'balanceMaxShift' => 1,
            'balanceCooldown' => 3,
        ],
    ],

    'environments' => [
        'production' => [
            'supervisor-default' => [
                'maxProcesses' => 10,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 3,
            ],
        ],

        'local' => [
            'supervisor-default' => [
                'maxProcesses' => 3,
            ],
        ],
    ],

];
