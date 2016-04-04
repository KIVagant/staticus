<?php
use Zend\Expressive\Application;
use Zend\Expressive\Container\ApplicationFactory;
use Zend\Expressive\Helper;

return [
    'voice' => [
        'cache_dir' => DATA_DIR . 'voice/',
        'file_extension' => VOICE_FILE_EXTENSION,
    ],
];
