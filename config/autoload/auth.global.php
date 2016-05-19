<?php

return [
    'dependencies' => [
        'factories' => [
            Zend\Session\SessionManager::class => Staticus\Auth\Factories\SessionManagerFactory::class,
        ],
    ],
    'auth' => [
        'session' => [
            'redis' => [
                    'host' => env('REDIS_HOST', true),
                    'port' => env('REDIS_PORT', true),
                    'password' => env('REDIS_PASS', true),
                ],
            'options' => [
                    'use_cookies' => env('SESSION_COOKIE_ENABLED', true),
                    'cookie_secure' => env('SESSION_COOKIE_SECURE', true),
                    'cookie_lifetime' => env('SESSION_GC_MAXLIFETIME', 31536000), // 1 year
                    'gc_maxlifetime' => env('SESSION_GC_MAXLIFETIME', 1728000), // 20 days
                    'name' => env('SESSION_NAMESPACE', true),
                ],
            ],
        'basic' => [
            'users' => [
                [
                    'name' => env('AUTH_DEFAULT_USER', 'Moderator'),
                    'pass' => env('AUTH_DEFAULT_USER_PASS', 'hasld1845aKAf29pp3nnzAAqkgHFjA1fEFWF3'),
                ],
            ],
        ],
    ],
];
