<?php

return [
    'dependencies' => [
        'invokables' => [
            Zend\Expressive\Router\RouterInterface::class => Zend\Expressive\Router\FastRouteRouter::class,

            Staticus\Auth\AuthBasicMiddleware::class => Staticus\Auth\AuthBasicMiddleware::class,
            Staticus\Auth\AuthSessionMiddleware::class => Staticus\Auth\AuthSessionMiddleware::class,
            Staticus\Acl\AclMiddleware::class => Staticus\Acl\AclMiddleware::class,
        ],
    ],
    'staticus' => [
        // Directory for cached files
        'data_dir' => DATA_DIR,

        // List of allowed namespaces
        // See the ACL config for each of them
        'namespaces' => [
            'fractal', // custom namespace
            \Staticus\Auth\UserInterface::NAMESPACES_WILDCARD,
        ],

        // If true and resource name is not valid and contains bad symbols, their will be converted to '-' for the end-point url.
        // If false – Bad request response will return.
        'clean_resource_name' => true,
        'images' => [
            // Allowed sizes: [[w, h], [w, h]]
            'sizes' => [
                [100, 100],
                [940, 532], // Courses-int, exercises, group B
                [600, 432], // Courses-int, exercises, group C
                [300, 172], // Course lesson header image
            ],

            // After-saving compression
            'compress' => [
                'compress' => true, // allow compression
                'quality' => 85, // compression in percents
                'interlace' => Imagick::INTERLACE_PLANE, // interlacing style
                'maxWidth' => 1024, // maximum allowed Width for a new image (when generated or downloaded)
                'maxHeight' => 1024, // maximum allowed Height for a new image (when generated or downloaded)
            ],
            'exif' => [
                'strip' => env('IMAGE_STRIP', false),
            ],
        ],
    ],
];
