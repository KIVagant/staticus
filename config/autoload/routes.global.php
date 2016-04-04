<?php

return [
    'dependencies' => [
        'invokables' => [
            Zend\Expressive\Router\RouterInterface::class => Zend\Expressive\Router\FastRouteRouter::class,
            Staticus\Action\VoiceActionGet::class => Staticus\Action\VoiceActionGet::class,
            Staticus\Action\VoiceActionPost::class => Staticus\Action\VoiceActionPost::class,
            Staticus\Action\VoiceActionDelete::class => Staticus\Action\VoiceActionDelete::class,
            AudioManager\Manager::class => AudioManager\Manager::class,
        ],
        'factories' => [
            AudioManager\Adapter\AdapterInterface::class => Staticus\Factory\VoiceAdapterFactory::class,
        ],
        // Для автоматического разрешения зависимостей на основе интерфейсов и абстракций
        // необходимо перечислить их типы (в нашем случае они совпадают с ключами в invokables и factories)
        // После этого эти типы можно использовать в type hinting.
        'types' => [
            AudioManager\Adapter\AdapterInterface::class => AudioManager\Adapter\AdapterInterface::class,
            Common\Config\Config::class => Common\Config\Config::class,
            AudioManager\Manager::class => AudioManager\Manager::class,
        ],
    ],
    'routes' => [
        // TODO Добавить проксик с провайдером динамических фрактальных картинок на основе хеш-суммы ключевой фразы из запроса (один запрос — всегда одна и та же картинка)
        [
            'name' => 'get-voice',
            'path' => '/{text:.+}.{extension:' . VOICE_FILE_EXTENSION . '}',
            'middleware' => Staticus\Action\VoiceActionGet::class,
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'post-voice',
            'path' => '/{text:.+}.{extension:' . VOICE_FILE_EXTENSION . '}', // ?recreate=1 is allowed here
            'middleware' => Staticus\Action\VoiceActionPost::class,
            'allowed_methods' => ['POST'],
        ],
        [
            'name' => 'delete-voice',
            'path' => '/{text:.+}.{extension:' . VOICE_FILE_EXTENSION . '}',
            'middleware' => Staticus\Action\VoiceActionDelete::class,
            'allowed_methods' => ['DELETE'],
        ],
    ],
];
