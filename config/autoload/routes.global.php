<?php

return [
    'dependencies' => [
        'invokables' => [
//            Staticus\Resources\File\ResourceDO::class => Staticus\Resources\File\ResourceDO::class,
            Staticus\Resources\Mpeg\ResourceDO::class => Staticus\Resources\Mpeg\ResourceDO::class,
//            Staticus\Resources\Gif\ResourceDO::class => Staticus\Resources\Gif\ResourceDO::class,
            Staticus\Resources\Jpg\ResourceDO::class => Staticus\Resources\Jpg\ResourceDO::class,
//            Staticus\Resources\Png\ResourceDO::class => Staticus\Resources\Png\ResourceDO::class,

            App\Actions\Voice\ActionGet::class => App\Actions\Voice\ActionGet::class,
            App\Actions\Voice\ActionPost::class => App\Actions\Voice\ActionPost::class,
            App\Actions\Voice\ActionDelete::class => App\Actions\Voice\ActionDelete::class,
            App\Actions\Voice\ActionList::class => App\Actions\Voice\ActionList::class,

            App\Actions\Image\ActionGet::class => App\Actions\Image\ActionGet::class,
            App\Actions\Image\ActionPost::class => App\Actions\Image\ActionPost::class,
            App\Actions\Image\ActionDelete::class => App\Actions\Image\ActionDelete::class,
            App\Actions\Image\ActionSearchJpg::class => App\Actions\Image\ActionSearchJpg::class,
            App\Actions\Image\ActionList::class => App\Actions\Image\ActionList::class,

            AudioManager\Manager::class => AudioManager\Manager::class,

            FractalManager\Manager::class => FractalManager\Manager::class,
            FractalManager\Adapter\AdapterInterface::class => FractalManager\Adapter\MandlebrotAdapter::class,

            SearchManager\Manager::class => SearchManager\Manager::class,
            SearchManager\Adapter\AdapterInterface::class => SearchManager\Adapter\GoogleAdapter::class,
            SearchManager\Image\ImageSearchInterface::class => SearchManager\Image\SearchImageProviderProxy::class, // search adapter proxy
            SearchManager\Image\GoogleCustomSearchImage::class => SearchManager\Image\GoogleCustomSearchImage::class, // search adapter

//            Staticus\Resources\File\PrepareResourceMiddleware::class => Staticus\Resources\File\PrepareResourceMiddleware::class,
//            Staticus\Resources\File\SaveResourceMiddleware::class => Staticus\Resources\File\SaveResourceMiddleware::class,
//            Staticus\Resources\File\ResourceResponseMiddleware::class => Staticus\Resources\File\ResourceResponseMiddleware::class,

            Staticus\Resources\Mpeg\PrepareResourceMiddleware::class => Staticus\Resources\Mpeg\PrepareResourceMiddleware::class,
            Staticus\Resources\Mpeg\SaveResourceMiddleware::class => Staticus\Resources\Mpeg\SaveResourceMiddleware::class,
            Staticus\Resources\Mpeg\ResourceResponseMiddleware::class => Staticus\Resources\Mpeg\ResourceResponseMiddleware::class,

//            Staticus\Resources\Gif\PrepareResourceMiddleware::class => Staticus\Resources\Gif\PrepareResourceMiddleware::class,
//            Staticus\Resources\Gif\PostProcessingMiddleware::class => Staticus\Resources\Gif\PostProcessingMiddleware::class,
//            Staticus\Resources\Gif\SaveResourceMiddleware::class => Staticus\Resources\Gif\SaveResourceMiddleware::class,
//            Staticus\Resources\Gif\ResourceResponseMiddleware::class => Staticus\Resources\Gif\ResourceResponseMiddleware::class,
//            Staticus\Resources\Gif\StripMiddleware::class => Staticus\Resources\Gif\StripMiddleware::class,

            Staticus\Resources\Jpg\PrepareResourceMiddleware::class => Staticus\Resources\Jpg\PrepareResourceMiddleware::class,
            Staticus\Resources\Jpg\ResizeMiddleware::class => Staticus\Resources\Jpg\ResizeMiddleware::class,
            Staticus\Resources\Jpg\CropMiddleware::class => Staticus\Resources\Jpg\CropMiddleware::class,
            Staticus\Resources\Jpg\SaveResourceMiddleware::class => Staticus\Resources\Jpg\SaveResourceMiddleware::class,
            Staticus\Resources\Jpg\ResourceResponseMiddleware::class => Staticus\Resources\Jpg\ResourceResponseMiddleware::class,
            Staticus\Resources\Jpg\StripMiddleware::class => Staticus\Resources\Jpg\StripMiddleware::class,

//            Staticus\Resources\Png\SaveResourceMiddleware::class => Staticus\Resources\Png\SaveResourceMiddleware::class,
//            Staticus\Resources\Png\PostProcessingMiddleware::class => Staticus\Resources\Png\PostProcessingMiddleware::class,
//            Staticus\Resources\Png\PrepareResourceMiddleware::class => Staticus\Resources\Png\PrepareResourceMiddleware::class,
//            Staticus\Resources\Png\ResourceResponseMiddleware::class => Staticus\Resources\Png\ResourceResponseMiddleware::class,
//            Staticus\Resources\Png\StripMiddleware::class => Staticus\Resources\Png\StripMiddleware::class,
        ],
        'factories' => [
            AudioManager\Adapter\AdapterInterface::class => App\Actions\Voice\VoiceAdapterFactory::class,
        ],
        // Для автоматического разрешения зависимостей на основе интерфейсов и абстракций
        // необходимо перечислить их типы (в нашем случае они совпадают с ключами в invokables и factories)
        // После этого эти типы можно использовать в type hinting.
        'types' => [
//            Staticus\Resources\File\ResourceDO::class => Staticus\Resources\File\ResourceDO::class,
            Staticus\Resources\Image\ResourceImageDOInterface::class => Staticus\Resources\Jpg\ResourceDO::class, // For Fractal\Action* injects
            Staticus\Resources\Mpeg\ResourceDO::class => Staticus\Resources\Mpeg\ResourceDO::class,
//            Staticus\Resources\Gif\ResourceDO::class => Staticus\Resources\Gif\ResourceDO::class,
            Staticus\Resources\Jpg\ResourceDO::class => Staticus\Resources\Jpg\ResourceDO::class,
//            Staticus\Resources\Png\ResourceDO::class => Staticus\Resources\Png\ResourceDO::class,
            AudioManager\Adapter\AdapterInterface::class => AudioManager\Adapter\AdapterInterface::class,
            AudioManager\Manager::class => AudioManager\Manager::class,
            FractalManager\Adapter\AdapterInterface::class => FractalManager\Adapter\AdapterInterface::class,
            FractalManager\Manager::class => FractalManager\Manager::class,
            SearchManager\Adapter\AdapterInterface::class => SearchManager\Adapter\AdapterInterface::class,
            SearchManager\Manager::class => SearchManager\Manager::class,
            SearchManager\Image\ImageSearchInterface::class => SearchManager\Image\ImageSearchInterface::class,
            SearchManager\Image\GoogleCustomSearchImage::class => SearchManager\Image\GoogleCustomSearchImage::class,
        ],
    ],
    'routes' => [
        [
            'name' => 'list-voice',
            'path' => '/{' . Staticus\Acl\Actions::ACTION_LIST . ':list}/{name:.+}.{type:' . Staticus\Resources\Mpeg\ResourceDO::TYPE . '}',
            'middleware' => [
                Staticus\Resources\Mpeg\PrepareResourceMiddleware::class,
                Staticus\Auth\AuthSessionMiddleware::class,
                Staticus\Auth\AuthBasicMiddleware::class,
                Staticus\Acl\AclMiddleware::class,
                App\Actions\Voice\ActionList::class,
            ],
            'allowed_methods' => ['GET', 'POST'],
        ],
        [
            'name' => 'get-voice',
            'path' => '/{name:.+}.{type:' . Staticus\Resources\Mpeg\ResourceDO::TYPE . '}',
            'middleware' => [
                Staticus\Resources\Mpeg\PrepareResourceMiddleware::class,
                Staticus\Auth\AuthSessionMiddleware::class,
                Staticus\Auth\AuthBasicMiddleware::class,
                Staticus\Acl\AclMiddleware::class,
                App\Actions\Voice\ActionGet::class,
            ],
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'post-voice',
            'path' => '/{name:.+}.{type:' . Staticus\Resources\Mpeg\ResourceDO::TYPE . '}', // ?recreate=1 is allowed here
            'middleware' => [
                Staticus\Resources\Mpeg\PrepareResourceMiddleware::class,
                Staticus\Auth\AuthSessionMiddleware::class,
                Staticus\Auth\AuthBasicMiddleware::class,
                Staticus\Acl\AclMiddleware::class,
                App\Actions\Voice\ActionPost::class,
                Staticus\Resources\Mpeg\SaveResourceMiddleware::class,
                Staticus\Resources\Mpeg\ResourceResponseMiddleware::class,
            ],
            'allowed_methods' => ['POST'],
        ],
        [
            'name' => 'delete-voice',
            'path' => '/{name:.+}.{type:' . Staticus\Resources\Mpeg\ResourceDO::TYPE . '}',
            'middleware' => [
                Staticus\Resources\Mpeg\PrepareResourceMiddleware::class,
                Staticus\Auth\AuthSessionMiddleware::class,
                Staticus\Auth\AuthBasicMiddleware::class,
                Staticus\Acl\AclMiddleware::class,
                App\Actions\Voice\ActionDelete::class,
            ],
            'allowed_methods' => ['DELETE'],
        ],

        /* JPG */
        [
            'name' => 'list-jpg',
            'path' => '/{' . Staticus\Acl\Actions::ACTION_LIST . ':list}/{name:.+}.{type:' . Staticus\Resources\Jpg\ResourceDO::TYPE . '}',
            'middleware' => [
                Staticus\Resources\Jpg\PrepareResourceMiddleware::class,
                Staticus\Auth\AuthSessionMiddleware::class,
                Staticus\Auth\AuthBasicMiddleware::class,
                Staticus\Acl\AclMiddleware::class,
                App\Actions\Image\ActionList::class,
            ],
            'allowed_methods' => ['GET', 'POST'],
        ],
        [
            'name' => 'search-jpg',
            'path' => '/{' . Staticus\Acl\Actions::ACTION_SEARCH . ':search}/{name:.+}.{type:' . Staticus\Resources\Jpg\ResourceDO::TYPE . '}',
            'middleware' => [
                Staticus\Resources\Jpg\PrepareResourceMiddleware::class,
                Staticus\Auth\AuthSessionMiddleware::class,
                Staticus\Auth\AuthBasicMiddleware::class,
                Staticus\Acl\AclMiddleware::class,
                App\Actions\Image\ActionSearchJpg::class,
            ],
            'allowed_methods' => ['GET', 'POST'],
        ],
        [
            'name' => 'get-jpg',
            'path' => '/{name:.+}.{type:' . Staticus\Resources\Jpg\ResourceDO::TYPE . '}',
            'middleware' => [
                Staticus\Resources\Jpg\PrepareResourceMiddleware::class,
                Staticus\Auth\AuthSessionMiddleware::class,
                Staticus\Auth\AuthBasicMiddleware::class,
                Staticus\Acl\AclMiddleware::class,
                Staticus\Resources\Jpg\ResizeMiddleware::class,
                App\Actions\Image\ActionGet::class,
            ],
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'post-jpg',
            'path' => '/{name:.+}.{type:' . Staticus\Resources\Jpg\ResourceDO::TYPE . '}',
            'allowed_methods' => ['POST'],
            'middleware' => [
                Staticus\Resources\Jpg\PrepareResourceMiddleware::class,
                Staticus\Auth\AuthSessionMiddleware::class,
                Staticus\Auth\AuthBasicMiddleware::class,
                Staticus\Acl\AclMiddleware::class,
                App\Actions\Image\ActionPost::class,
                Staticus\Resources\Jpg\SaveResourceMiddleware::class,
                Staticus\Resources\Jpg\StripMiddleware::class,
                Staticus\Resources\Jpg\CropMiddleware::class,
                Staticus\Resources\Jpg\ResizeMiddleware::class,
                Staticus\Resources\Jpg\ResourceResponseMiddleware::class,
            ],
        ],
        [
            'name' => 'delete-jpg',
            'path' => '/{name:.+}.{type:' . Staticus\Resources\Jpg\ResourceDO::TYPE . '}',
            'middleware' => [
                Staticus\Resources\Jpg\PrepareResourceMiddleware::class,
                Staticus\Auth\AuthSessionMiddleware::class,
                Staticus\Auth\AuthBasicMiddleware::class,
                Staticus\Acl\AclMiddleware::class,
                App\Actions\Image\ActionDelete::class,
            ],
            'allowed_methods' => ['DELETE'],
        ],
    ],
];
