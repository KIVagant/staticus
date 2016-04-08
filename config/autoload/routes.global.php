<?php

return [
    'dependencies' => [
        'invokables' => [
            Zend\Expressive\Router\RouterInterface::class => Zend\Expressive\Router\FastRouteRouter::class,
            App\Auth\AuthBasicMiddleware::class => App\Auth\AuthBasicMiddleware::class,
            Staticus\Resource\ResourceDO::class => Staticus\Resource\ResourceDO::class,
            Staticus\Action\Voice\ActionGet::class => Staticus\Action\Voice\ActionGet::class,
            Staticus\Action\Voice\ActionPost::class => Staticus\Action\Voice\ActionPost::class,
            Staticus\Action\Voice\ActionDelete::class => Staticus\Action\Voice\ActionDelete::class,
            Staticus\Action\Fractal\ActionGet::class => Staticus\Action\Fractal\ActionGet::class,
            Staticus\Action\Fractal\ActionPost::class => Staticus\Action\Fractal\ActionPost::class,
            Staticus\Action\Fractal\ActionDelete::class => Staticus\Action\Fractal\ActionDelete::class,
            AudioManager\Manager::class => AudioManager\Manager::class,
            FractalManager\Manager::class => FractalManager\Manager::class,
            FractalManager\Adapter\AdapterInterface::class => FractalManager\Adapter\Mandlebrot::class,
        ],
        'factories' => [
            AudioManager\Adapter\AdapterInterface::class => Staticus\Action\Voice\VoiceAdapterFactory::class,
            Staticus\Resource\PrepareResourceMiddleware::class => Staticus\Resource\PrepareResourceMiddlewareFactory::class,
            App\Resources\SaveFileMiddleware::class => App\Resources\SaveFileMiddlewareFactory::class,
//            App\Resources\SaveGifMiddleware::class => App\Resources\SaveJpgMiddlewareFactory::class,
            App\Resources\SaveJpgMiddleware::class => App\Resources\SaveJpgMiddlewareFactory::class,
//            App\Resources\SavePngMiddleware::class => App\Resources\SaveJpgMiddlewareFactory::class,
        ],
        // Для автоматического разрешения зависимостей на основе интерфейсов и абстракций
        // необходимо перечислить их типы (в нашем случае они совпадают с ключами в invokables и factories)
        // После этого эти типы можно использовать в type hinting.
        'types' => [
            Common\Config\Config::class => Common\Config\Config::class,
            Staticus\Resource\ResourceDO::class => Staticus\Resource\ResourceDO::class,
            AudioManager\Adapter\AdapterInterface::class => AudioManager\Adapter\AdapterInterface::class,
            AudioManager\Manager::class => AudioManager\Manager::class,
            FractalManager\Adapter\AdapterInterface::class => FractalManager\Adapter\AdapterInterface::class,
            FractalManager\Manager::class => FractalManager\Manager::class,
        ],
    ],
    'routes' => [
        [
            'name' => 'get-voice',
            'path' => '/{name:.+}.{type:' . VOICE_FILE_TYPE . '}',
            'middleware' => [
                Staticus\Resource\PrepareResourceMiddleware::class,
                Staticus\Action\Voice\ActionGet::class,
            ],
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'post-voice',
            'path' => '/{name:.+}.{type:' . VOICE_FILE_TYPE . '}', // ?recreate=1 is allowed here
            'middleware' => [
                App\Auth\AuthBasicMiddleware::class,
                Staticus\Resource\PrepareResourceMiddleware::class,
                Staticus\Action\Voice\ActionPost::class,
                App\Resources\SaveFileMiddleware::class,
            ],
            'allowed_methods' => ['POST'],
        ],
        [
            'name' => 'delete-voice',
            'path' => '/{name:.+}.{type:' . VOICE_FILE_TYPE . '}',
            'middleware' => [
                App\Auth\AuthBasicMiddleware::class,
                Staticus\Resource\PrepareResourceMiddleware::class,
                Staticus\Action\Voice\ActionDelete::class,
            ],
            'allowed_methods' => ['DELETE'],
        ],
        [
            'name' => 'get-fractal',
            'path' => '/fractal/{name:.+}.{type:' . FRACTAL_FILE_TYPE . '}',
            'middleware' => [
                Staticus\Resource\PrepareResourceMiddleware::class,
                Staticus\Action\Fractal\ActionGet::class,
            ],
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'post-fractal',
            'path' => '/fractal/{name:.+}.{type:' . FRACTAL_FILE_TYPE . '}',
            'allowed_methods' => ['POST'],
            'middleware' => [
                App\Auth\AuthBasicMiddleware::class,
                Staticus\Resource\PrepareResourceMiddleware::class,
                Staticus\Action\Fractal\ActionPost::class,
                App\Resources\SaveJpgMiddleware::class,
            ],
        ],
        [
            'name' => 'delete-fractal',
            'path' => '/fractal/{name:.+}.{type:' . FRACTAL_FILE_TYPE . '}',
            'middleware' => [
                App\Auth\AuthBasicMiddleware::class,
                Staticus\Resource\PrepareResourceMiddleware::class,
                Staticus\Action\Fractal\ActionDelete::class,
            ],
            'allowed_methods' => ['DELETE'],
        ],
    ],
];
