<?php
define('REQUEST_MICROTIME', microtime(true));
chdir(dirname(__DIR__));

// Delegate static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server'
    && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
) {
    return false;
}
require 'vendor/autoload.php';

$dotenv = new Dotenv\Dotenv('./');
$dotenv->load();

define('PROJECT_DIR', dirname(__DIR__ . '../'));
define('DATA_DIR',  env('DATA_DIR', PROJECT_DIR . DIRECTORY_SEPARATOR . 'data/'));
define('VOICE_FILE_TYPE', 'mp3');
define('FRACTAL_FILE_TYPE', 'jpg');

/** @var \Interop\Container\ContainerInterface $container */
$container = require 'config/container.php';

/** @var \Zend\Expressive\Application $app */
$app = $container->get(\Zend\Expressive\Application::class);
$app->run();
exit;