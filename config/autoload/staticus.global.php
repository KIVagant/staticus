<?php

return [
    'dependencies' => [
        'invokables' => [
            Zend\Expressive\Router\RouterInterface::class => Zend\Expressive\Router\FastRouteRouter::class,
        ],
    ],
    'staticus' => [
        // Directory for cached files
        'data_dir' => DATA_DIR,

        // List of allowed namespaces
        'namespaces' => [
            'fractal',
            'user/*', // @todo wildcards support
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
        ],
    ],
];
