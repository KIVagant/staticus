<?php

return [
    'dependencies' => [
        'invokables' => [
            Zend\Expressive\Router\RouterInterface::class => Zend\Expressive\Router\FastRouteRouter::class,
            Staticus\Auth\AuthBasicMiddleware::class => Staticus\Auth\AuthBasicMiddleware::class,
//            Staticus\Resources\File\ResourceDO::class => Staticus\Resources\File\ResourceDO::class,
            Staticus\Resources\Mpeg\ResourceDO::class => Staticus\Resources\Mpeg\ResourceDO::class,
//            Staticus\Resources\Gif\ResourceDO::class => Staticus\Resources\Gif\ResourceDO::class,
            Staticus\Resources\Jpg\ResourceDO::class => Staticus\Resources\Jpg\ResourceDO::class,
//            Staticus\Resources\Png\ResourceDO::class => Staticus\Resources\Png\ResourceDO::class,
            App\Actions\Voice\ActionGet::class => App\Actions\Voice\ActionGet::class,
            App\Actions\Voice\ActionPost::class => App\Actions\Voice\ActionPost::class,
            App\Actions\Voice\ActionDelete::class => App\Actions\Voice\ActionDelete::class,
            App\Actions\Fractal\ActionGet::class => App\Actions\Fractal\ActionGet::class,
            App\Actions\Fractal\ActionPost::class => App\Actions\Fractal\ActionPost::class,
            App\Actions\Fractal\ActionDelete::class => App\Actions\Fractal\ActionDelete::class,
            AudioManager\Manager::class => AudioManager\Manager::class,
            FractalManager\Manager::class => FractalManager\Manager::class,
            FractalManager\Adapter\AdapterInterface::class => FractalManager\Adapter\Mandlebrot::class,
//            Staticus\Resources\File\PrepareResourceMiddleware::class => Staticus\Resources\File\PrepareResourceMiddleware::class,
//            Staticus\Resources\File\SaveResourceMiddleware::class => Staticus\Resources\File\SaveResourceMiddleware::class,
            Staticus\Resources\Mpeg\PrepareResourceMiddleware::class => Staticus\Resources\Mpeg\PrepareResourceMiddleware::class,
            Staticus\Resources\Mpeg\SaveResourceMiddleware::class => Staticus\Resources\Mpeg\SaveResourceMiddleware::class,
//            Staticus\Resources\Gif\PrepareResourceMiddleware::class => Staticus\Resources\Gif\PrepareResourceMiddleware::class,
//            Staticus\Resources\Gif\SaveResourceMiddleware::class => Staticus\Resources\Gif\SaveResourceMiddleware::class,
            Staticus\Resources\Jpg\PrepareResourceMiddleware::class => Staticus\Resources\Jpg\PrepareResourceMiddleware::class,
            Staticus\Resources\Jpg\SaveResourceMiddleware::class => Staticus\Resources\Jpg\SaveResourceMiddleware::class,
//            Staticus\Resources\Png\PrepareResourceMiddleware::class => Staticus\Resources\Png\PrepareResourceMiddleware::class,
//            Staticus\Resources\Png\SaveResourceMiddleware::class => Staticus\Resources\Png\SaveResourceMiddleware::class,
        ],
        'factories' => [
            AudioManager\Adapter\AdapterInterface::class => App\Actions\Voice\VoiceAdapterFactory::class,
        ],
        // Для автоматического разрешения зависимостей на основе интерфейсов и абстракций
        // необходимо перечислить их типы (в нашем случае они совпадают с ключами в invokables и factories)
        // После этого эти типы можно использовать в type hinting.
        'types' => [
            Staticus\Config\Config::class => Staticus\Config\Config::class,
//            Staticus\Resources\File\ResourceDO::class => Staticus\Resources\File\ResourceDO::class,
            Staticus\Resources\ResourceImageDOInterface::class => Staticus\Resources\Jpg\ResourceDO::class, // For Fractal\Action* injects
            Staticus\Resources\Mpeg\ResourceDO::class => Staticus\Resources\Mpeg\ResourceDO::class,
//            Staticus\Resources\Gif\ResourceDO::class => Staticus\Resources\Gif\ResourceDO::class,
            Staticus\Resources\Jpg\ResourceDO::class => Staticus\Resources\Jpg\ResourceDO::class,
//            Staticus\Resources\Png\ResourceDO::class => Staticus\Resources\Png\ResourceDO::class,
            AudioManager\Adapter\AdapterInterface::class => AudioManager\Adapter\AdapterInterface::class,
            AudioManager\Manager::class => AudioManager\Manager::class,
            FractalManager\Adapter\AdapterInterface::class => FractalManager\Adapter\AdapterInterface::class,
            FractalManager\Manager::class => FractalManager\Manager::class,
        ],
    ],
    'routes' => [
        [
            'name' => 'get-voice',
            'path' => '/{name:.+}.{type:' . Staticus\Resources\Mpeg\ResourceDO::TYPE . '}',
            'middleware' => [
                Staticus\Resources\Mpeg\PrepareResourceMiddleware::class,
                App\Actions\Voice\ActionGet::class,
            ],
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'post-voice',
            'path' => '/{name:.+}.{type:' . Staticus\Resources\Mpeg\ResourceDO::TYPE . '}', // ?recreate=1 is allowed here
            'middleware' => [
                Staticus\Auth\AuthBasicMiddleware::class,
                Staticus\Resources\Mpeg\PrepareResourceMiddleware::class,
                App\Actions\Voice\ActionPost::class,
                Staticus\Resources\Mpeg\SaveResourceMiddleware::class,
            ],
            'allowed_methods' => ['POST'],
        ],
        [
            'name' => 'delete-voice',
            'path' => '/{name:.+}.{type:' . Staticus\Resources\Mpeg\ResourceDO::TYPE . '}',
            'middleware' => [
                Staticus\Auth\AuthBasicMiddleware::class,
                Staticus\Resources\Mpeg\PrepareResourceMiddleware::class,
                App\Actions\Voice\ActionDelete::class,
            ],
            'allowed_methods' => ['DELETE'],
        ],
        [
            'name' => 'get-fractal',
            'path' => '/fractal/{name:.+}.{type:' . Staticus\Resources\Jpg\ResourceDO::TYPE . '}',
            'middleware' => [
                Staticus\Resources\Jpg\PrepareResourceMiddleware::class,
                App\Actions\Fractal\ActionGet::class,
            ],
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'post-fractal',
            'path' => '/fractal/{name:.+}.{type:' . Staticus\Resources\Jpg\ResourceDO::TYPE . '}',
            'allowed_methods' => ['POST'],
            'middleware' => [
                Staticus\Auth\AuthBasicMiddleware::class,
                Staticus\Resources\Jpg\PrepareResourceMiddleware::class,
                App\Actions\Fractal\ActionPost::class,
                Staticus\Resources\Jpg\SaveResourceMiddleware::class,
            ],
        ],
        [
            'name' => 'delete-fractal',
            'path' => '/fractal/{name:.+}.{type:' . Staticus\Resources\Jpg\ResourceDO::TYPE . '}',
            'middleware' => [
                Staticus\Auth\AuthBasicMiddleware::class,
                Staticus\Resources\Jpg\PrepareResourceMiddleware::class,
                App\Actions\Fractal\ActionDelete::class,
            ],
            'allowed_methods' => ['DELETE'],
        ],
    ],
];
