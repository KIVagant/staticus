<?php

return [
    'dependencies' => [
        'invokables' => [
            Zend\Expressive\Router\RouterInterface::class => Zend\Expressive\Router\FastRouteRouter::class,
            Voice\Action\GenerateAudioAction::class => Voice\Action\GenerateAudioAction::class,
            AudioManager\Manager::class => AudioManager\Manager::class,
        ],
        'factories' => [
            AudioManager\Adapter\AdapterInterface::class => Voice\Factory\AudioAdapterFactory::class,
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
            'name' => 'voice',
            'path' => '/{text:.+}.{extension:' . VOICE_FILE_EXTENSION . '}',
            'middleware' => Voice\Action\GenerateAudioAction::class,
            'allowed_methods' => ['GET'],
        ],
    ],
];
