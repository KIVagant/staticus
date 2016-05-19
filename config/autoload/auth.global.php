<?php

return [
    'dependencies' => [
        'factories' => [
            Zend\Session\ManagerInterface::class => Staticus\Auth\Factories\SessionManagerFactory::class,
        ],
        'types' => [
            Zend\Session\ManagerInterface::class => Zend\Session\ManagerInterface::class,
        ],
    ],
    'auth' => [
        'session' => [
            'redis' => [
                    'host' => env('REDIS_HOST', '127.0.0.1'),
                    'port' => env('REDIS_PORT', 6379),
                    'password' => env('REDIS_PASS'),
                ],
            'options' => [
                    'use_cookies' => env('SESSION_COOKIE_ENABLED', true),
                    'cookie_secure' => env('SESSION_COOKIE_SECURE', true),
                    'cookie_lifetime' => env('SESSION_GC_MAXLIFETIME', 31536000), // 1 year
                    'gc_maxlifetime' => env('SESSION_GC_MAXLIFETIME', 1728000), // 20 days
                    'name' => env('SESSION_COOKIE_NAME'),
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
