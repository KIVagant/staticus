<?php

return [
    'debug' => env('ZEND_DEBUG', false),

    'config_cache_enabled' => env('ZEND_CONFIG_CACHE', false),

    'zend-expressive' => [
        'error_handler' => [
            'template_404'   => 'error::404',
            'template_error' => 'error::error',
        ],
    ],
];
