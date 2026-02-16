<?php
// config/weather.php

return [
    'api' => [
        'openweather' => [
            'key' => env('OPENWEATHER_API_KEY', '18763be5923a94cc59f5d40f3742ecff'),
            'base_url' => env('OPENWEATHER_BASE_URL', 'https://api.openweathermap.org/data/2.5'),
            'units' => 'metric',
            'lang' => 'ar',
        ],
        'backup' => [
            'openmeteo' => [
                'enabled' => true,
                'base_url' => 'https://api.open-meteo.com/v1',
                'geocoding_url' => 'https://geocoding-api.open-meteo.com/v1',
            ]
        ]
    ],

    'cache' => [
        'enabled' => true,
        'ttl' => [
            'current' => 600,      // 10 دقائق
            'forecast' => 1800,     // 30 دقيقة
            'location' => 86400,    // 24 ساعة
        ]
    ],

    'units' => [
        'default' => 'metric',
        'options' => ['metric', 'imperial']
    ]
];
