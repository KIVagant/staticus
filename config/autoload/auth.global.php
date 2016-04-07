<?php

return [
    'auth' => [
        'basic' => [
            // Place here middlewares, that should be protected by App\Auth\AuthBasicMiddleware
            'users' => [
                env('AUTH_DEFAULT_USER', 'Moderator:hasld1845aKAf29pp3nnzAAqkgHFjA1fEFWF3'),
            ],
        ],
    ],
];
