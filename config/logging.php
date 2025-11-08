<?php

$basePath = dirname(__DIR__);

return [
    'default' => env('LOG_CHANNEL', 'stack'),

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single'],
        ],

        'single' => [
            'driver' => 'single',
            'path' => $basePath . '/storage/logs/laravel.log',
            'level' => env('LOG_LEVEL', 'debug'),
        ],
    ],
];
