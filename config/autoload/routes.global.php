<?php

return [
    'dependencies' => [
        'invokables' => [
            Zend\Expressive\Router\RouterInterface::class => Zend\Expressive\Router\FastRouteRouter::class,
            App\Auth\AuthBasicMiddleware::class => App\Auth\AuthBasicMiddleware::class,
            App\Resources\File\ResourceFileDO::class => App\Resources\File\ResourceFileDO::class,
            App\Resources\ResourceImageDO::class => App\Resources\ResourceImageDO::class,
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
            App\Resources\File\PrepareResourceMiddleware::class => App\Resources\File\PrepareResourceMiddlewareFactory::class,
            App\Resources\File\SaveResourceMiddleware::class => App\Resources\File\SaveResourceMiddlewareFactory::class,
            App\Resources\Gif\PrepareResourceMiddleware::class => App\Resources\Gif\PrepareResourceMiddlewareFactory::class,
            App\Resources\Gif\SaveResourceMiddleware::class => App\Resources\Gif\SaveResourceMiddlewareFactory::class,
            App\Resources\Jpg\PrepareResourceMiddleware::class => App\Resources\Jpg\PrepareResourceMiddlewareFactory::class,
            App\Resources\Jpg\SaveResourceMiddleware::class => App\Resources\Jpg\SaveResourceMiddlewareFactory::class,
            App\Resources\Png\PrepareResourceMiddleware::class => App\Resources\Png\PrepareResourceMiddlewareFactory::class,
            App\Resources\Png\SaveResourceMiddleware::class => App\Resources\Png\SaveResourceMiddlewareFactory::class,
        ],
        // Для автоматического разрешения зависимостей на основе интерфейсов и абстракций
        // необходимо перечислить их типы (в нашем случае они совпадают с ключами в invokables и factories)
        // После этого эти типы можно использовать в type hinting.
        'types' => [
            Common\Config\Config::class => Common\Config\Config::class,
            App\Resources\File\ResourceFileDO::class => App\Resources\File\ResourceFileDO::class,
            App\Resources\ResourceImageDO::class => App\Resources\ResourceImageDO::class,
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
                App\Resources\File\PrepareResourceMiddleware::class,
                Staticus\Action\Voice\ActionGet::class,
            ],
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'post-voice',
            'path' => '/{name:.+}.{type:' . VOICE_FILE_TYPE . '}', // ?recreate=1 is allowed here
            'middleware' => [
                App\Auth\AuthBasicMiddleware::class,
                App\Resources\File\PrepareResourceMiddleware::class,
                Staticus\Action\Voice\ActionPost::class,
                App\Resources\File\SaveResourceMiddleware::class,
            ],
            'allowed_methods' => ['POST'],
        ],
        [
            'name' => 'delete-voice',
            'path' => '/{name:.+}.{type:' . VOICE_FILE_TYPE . '}',
            'middleware' => [
                App\Auth\AuthBasicMiddleware::class,
                App\Resources\File\PrepareResourceMiddleware::class,
                Staticus\Action\Voice\ActionDelete::class,
            ],
            'allowed_methods' => ['DELETE'],
        ],
        [
            'name' => 'get-fractal',
            'path' => '/fractal/{name:.+}.{type:' . FRACTAL_FILE_TYPE . '}',
            'middleware' => [
                App\Resources\Jpg\PrepareResourceMiddleware::class,
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
                App\Resources\Jpg\PrepareResourceMiddleware::class,
                Staticus\Action\Fractal\ActionPost::class,
                App\Resources\Jpg\SaveResourceMiddleware::class,
            ],
        ],
        [
            'name' => 'delete-fractal',
            'path' => '/fractal/{name:.+}.{type:' . FRACTAL_FILE_TYPE . '}',
            'middleware' => [
                App\Auth\AuthBasicMiddleware::class,
                App\Resources\Jpg\PrepareResourceMiddleware::class,
                Staticus\Action\Fractal\ActionDelete::class,
            ],
            'allowed_methods' => ['DELETE'],
        ],
    ],
];
