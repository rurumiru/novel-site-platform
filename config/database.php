<?php

use Illuminate\Support\Str;

return [

    'default' => env('DB_CONNECTION', 'pgsql'),

    'connections' => [

        'pgsql' => [
            'driver'         => 'pgsql',
            'url'            => env('DATABASE_URL'),
            'host'           => env('DB_HOST', '127.0.0.1'),
            'port'           => env('DB_PORT', '5432'),
            'database'       => env('DB_DATABASE', 'laravel'),
            'username'       => env('DB_USERNAME', 'forge'),
            'password'       => env('DB_PASSWORD', ''),
            'charset'        => env('DB_CHARSET', 'utf8'),
            'prefix'         => '',
            'prefix_indexes' => true,
            'search_path'    => env('DB_SEARCH_PATH', 'public'),
            'sslmode'        => env('DB_SSLMODE', 'prefer'),
            'application_name' => env('DB_APP_NAME', env('APP_NAME', 'laravel')),
        ],

        'pgsql_beta' => [
            'driver'         => 'pgsql',
            'url'            => env('DATABASE_BETA_URL'),
            'host'           => env('DB_BETA_HOST', env('DB_HOST', '127.0.0.1')),
            'port'           => env('DB_BETA_PORT', env('DB_PORT', '5432')),
            'database'       => env('DB_BETA_DATABASE', 'laravel_beta'),
            'username'       => env('DB_BETA_USERNAME', env('DB_USERNAME', 'forge')),
            'password'       => env('DB_BETA_PASSWORD', env('DB_PASSWORD', '')),
            'charset'        => env('DB_CHARSET', 'utf8'),
            'prefix'         => '',
            'prefix_indexes' => true,
            'search_path'    => env('DB_BETA_SEARCH_PATH', env('DB_SEARCH_PATH', 'public')),
            'sslmode'        => env('DB_BETA_SSLMODE', env('DB_SSLMODE', 'prefer')),
            'application_name' => env('DB_APP_NAME', env('APP_NAME', 'laravel')) . '-beta',
        ],

        'sqlite' => [
            'driver'                  => 'sqlite',
            'url'                     => env('DATABASE_URL'),
            'database'                => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix'                  => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix'  => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'laravel'), '_') . '_database_'),
            'persistent' => (bool) env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'read_timeout'  => env('REDIS_READ_TIMEOUT', 60),
            'context'       => ['stream' => ['verify_peer' => false]],
        ],

        'cache' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            'read_timeout' => env('REDIS_READ_TIMEOUT', 60),
        ],

        'session' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_SESSION_DB', '2'),
            'read_timeout' => env('REDIS_READ_TIMEOUT', 60),
        ],

        'queue' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_QUEUE_DB', '3'),
            'read_timeout' => env('REDIS_READ_TIMEOUT', 60),
        ],

        'horizon' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_HORIZON_DB', '4'),
            'options'  => ['prefix' => env('HORIZON_PREFIX', 'horizon:')],
            'read_timeout' => env('REDIS_READ_TIMEOUT', 60),
        ],

    ],

];
