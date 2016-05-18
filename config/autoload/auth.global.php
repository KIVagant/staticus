<?php

return [
    'auth' => [
        'session' => [
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
