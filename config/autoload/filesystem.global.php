<?php

return [
    'dependencies' => [
        'invokables' => [
        ],
        'factories' => [
            League\Flysystem\AdapterInterface::class => Staticus\FileSystem\LocalAdapterFactory::class,
            League\Flysystem\FilesystemInterface::class => Staticus\FileSystem\FilesystemFactory::class,
        ],
        'types' => [
            League\Flysystem\AdapterInterface::class => League\Flysystem\AdapterInterface::class,
            League\Flysystem\FilesystemInterface::class => League\Flysystem\FilesystemInterface::class,
        ],
    ],
    'filesystem' => [
        'adapters' => [
            \League\Flysystem\Adapter\Local::class => [
                'options' => [
                    'root' => '/', // Must be a root dir until all file operations will be refactored with Filesystem
                ]
            ],
        ],
    ],
];
