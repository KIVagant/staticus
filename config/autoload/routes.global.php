<?php

return [
    'dependencies' => [
        'invokables' => [
            Zend\Expressive\Router\RouterInterface::class => Zend\Expressive\Router\FastRouteRouter::class,
            \Staticus\Action\Voice\ActionGet::class => \Staticus\Action\Voice\ActionGet::class,
            \Staticus\Action\Voice\ActionPost::class => \Staticus\Action\Voice\ActionPost::class,
            \Staticus\Action\Voice\ActionDelete::class => \Staticus\Action\Voice\ActionDelete::class,
            \Staticus\Action\Fractal\ActionGet::class => \Staticus\Action\Fractal\ActionGet::class,
            \Staticus\Action\Fractal\ActionPost::class => \Staticus\Action\Fractal\ActionPost::class,
            \Staticus\Action\Fractal\ActionDelete::class => \Staticus\Action\Fractal\ActionDelete::class,
            AudioManager\Manager::class => AudioManager\Manager::class,
            FractalManager\Manager::class => FractalManager\Manager::class,
            FractalManager\Adapter\AdapterInterface::class => FractalManager\Adapter\Mandlebrot::class,
        ],
        'factories' => [
            AudioManager\Adapter\AdapterInterface::class => Staticus\Factory\VoiceAdapterFactory::class,
        ],
        // Для автоматического разрешения зависимостей на основе интерфейсов и абстракций
        // необходимо перечислить их типы (в нашем случае они совпадают с ключами в invokables и factories)
        // После этого эти типы можно использовать в type hinting.
        'types' => [
            Common\Config\Config::class => Common\Config\Config::class,
            AudioManager\Adapter\AdapterInterface::class => AudioManager\Adapter\AdapterInterface::class,
            AudioManager\Manager::class => AudioManager\Manager::class,
            FractalManager\Adapter\AdapterInterface::class => FractalManager\Adapter\AdapterInterface::class,
            FractalManager\Manager::class => FractalManager\Manager::class,
        ],
    ],
    'routes' => [
        [
            'name' => 'get-voice',
            'path' => '/{text:.+}.{extension:' . VOICE_FILE_EXTENSION . '}',
            'middleware' => \Staticus\Action\Voice\ActionGet::class,
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'post-voice',
            'path' => '/{text:.+}.{extension:' . VOICE_FILE_EXTENSION . '}', // ?recreate=1 is allowed here
            'middleware' => \Staticus\Action\Voice\ActionPost::class,
            'allowed_methods' => ['POST'],
        ],
        [
            'name' => 'delete-voice',
            'path' => '/{text:.+}.{extension:' . VOICE_FILE_EXTENSION . '}',
            'middleware' => \Staticus\Action\Voice\ActionDelete::class,
            'allowed_methods' => ['DELETE'],
        ],
        [
            'name' => 'get-fractal',
            'path' => '/fractal/{text:.+}.{extension:' . FRACTAL_FILE_EXTENSION . '}',
            'middleware' => \Staticus\Action\Fractal\ActionGet::class,
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'post-fractal',
            'path' => '/fractal/{text:.+}.{extension:' . FRACTAL_FILE_EXTENSION . '}',
            'middleware' => \Staticus\Action\Fractal\ActionPost::class,
            'allowed_methods' => ['POST'],
        ],
        [
            'name' => 'delete-fractal',
            'path' => '/fractal/{text:.+}.{extension:' . FRACTAL_FILE_EXTENSION . '}',
            'middleware' => \Staticus\Action\Fractal\ActionDelete::class,
            'allowed_methods' => ['DELETE'],
        ],
    ],
];
