<?php

return [
    'auth' => [
        'basic' => [
            // Place here middlewares, that should be protected by App\Auth\AuthBasicMiddleware
            'users' => [
                env('AUTH_DEFAULT_USER', 'Moderator:hasld1845aKAf29pp3nnzAAqkgHFjA1fEFWF3'),
            ],
            'middlewares' => [
//                Staticus\Action\Voice\VoiceActionGet::class,
                \Staticus\Action\Voice\VoiceActionPost::class,
                \Staticus\Action\Voice\VoiceActionDelete::class,
            ],
        ],
    ],
];
