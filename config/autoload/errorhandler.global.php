<?php

if (env('ERROR_HANDLER', false)) {
    return [
        'error_handler' => true,
        'dependencies' => [
            'invokables' => [
                'Zend\Expressive\Whoops' => Whoops\Run::class,
                'Zend\Expressive\WhoopsPageHandler' => Whoops\Handler\PrettyPageHandler::class,
            ],
            'factories' => [
                'Zend\Expressive\FinalHandler' => Zend\Expressive\Container\WhoopsErrorHandlerFactory::class,
            ],
        ],

        'whoops' => [
            'json_exceptions' => [
                'display' => true,
                'show_trace' => true,
                'ajax_only' => true,
            ],
        ],
    ];
} else {
    return [
        'error_handler' => true,
    ];
}