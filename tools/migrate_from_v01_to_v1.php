<?php
use Staticus\Resources\ResourceDOAbstract;

define('REQUEST_MICROTIME', microtime(true));
chdir(dirname(__DIR__));

// Delegate static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server'
    && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
) {
    return false;
}
require 'vendor/autoload.php';
require 'config/bootstrap.php';

/** @var \Interop\Container\ContainerInterface $container */
$container = require 'config/container.php';

/** @var \League\Flysystem\FilesystemInterface $files */
$filesystem = $container->get(\League\Flysystem\FilesystemInterface::class);

$files = $filesystem->listContents(env('DATA_DIR'), true);

function migrateMp3($value, $key, $args)
{
    $path = '/' . $value['path'];
    $extension = $args['extension'];
    $regexp = '/(?P<datadir>' . str_replace('/', '\/', env('DATA_DIR')) . ')'
        . '(?P<namespace>[' . \Staticus\Resources\ResourceDOInterface::NAMESPACE_REG_SYMBOLS . ']+)?'
        . $extension . '\/(?P<variant>[' . \Staticus\Resources\ResourceDOInterface::VARIANT_REG_SYMBOLS . ']+)'
        . '\/(?P<version>[0-9]+)'
        . '\/(?P<name>[' . \Staticus\Resources\ResourceDOInterface::NAME_REG_SYMBOLS . ']+).' . $extension . '$/iu';

    /** @var array $matched */
    $matched = preg_match($regexp, $path, $matches);
    if ('file' === $value['type'] && $extension === $value['extension'] && $matched) {
        $shardVariant = substr($matches['variant'], 0, ResourceDOAbstract::SHARD_SLICE_LENGTH);
        $shardFilename = substr($matches['name'], 0, ResourceDOAbstract::SHARD_SLICE_LENGTH);
        $new_path = env('DATA_DIR')
            . $matches['namespace']
            . $extension . '/'
            . $shardVariant . '/'
            . $matches['variant'] . '/'
            . $matches['version'] . '/'
            . $shardFilename . '/'
            . $matches['name'] . '.' . $extension;
        migrateRun($path, $new_path);
    }
}

function migrateImage($value, $key, $args)
{
    $path = '/' . $value['path'];
    $extension = $args['extension'];
    $regexp = '/(?P<datadir>' . str_replace('/', '\/', env('DATA_DIR')) . ')'
        . '(?P<namespace>[' . \Staticus\Resources\ResourceDOInterface::NAMESPACE_REG_SYMBOLS . ']+)?'
        . $extension . '\/(?P<variant>[' . \Staticus\Resources\ResourceDOInterface::VARIANT_REG_SYMBOLS . ']+)'
        . '\/(?P<version>[0-9]+)'
        . '\/(?P<size>[0-9]+(x[0-9]+)?)'
        . '\/(?P<name>[' . \Staticus\Resources\ResourceDOInterface::NAME_REG_SYMBOLS . ']+).' . $extension . '$/iu';

    /** @var array $matched */
    $matched = preg_match($regexp, $path, $matches);
    if ('file' === $value['type'] && $extension === $value['extension'] && $matched) {
        $shardVariant = substr($matches['variant'], 0, ResourceDOAbstract::SHARD_SLICE_LENGTH);
        $shardFilename = substr($matches['name'], 0, ResourceDOAbstract::SHARD_SLICE_LENGTH);
        $new_path = env('DATA_DIR')
            . $matches['namespace']
            . $extension . '/'
            . $shardVariant . '/'
            . $matches['variant'] . '/'
            . $matches['version'] . '/'
            . $shardFilename . '/'
            . $matches['size'] . '/'
            . $matches['name'] . '.' . $extension;
        migrateRun($path, $new_path);
    }
}

/**
 * @param $path
 * @param $new_path
 */
function migrateRun($path, $new_path)
{
    /** @var \League\Flysystem\FilesystemInterface $files */
    global $filesystem;

    echo "Move this file? [y/n]:" . PHP_EOL;
    echo '     source: ' . $path . PHP_EOL;
    echo 'destination: ' . $new_path . PHP_EOL;
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (trim($line) != 'y') {
        echo "ABORTING!\n";
        fclose($handle);
        exit;
    }
    fclose($handle);
    try {
        if (!$filesystem->rename($path, $new_path)) {
            throw new \RuntimeException('Moving operation failed:' . PHP_EOL
                . '     source: ' . $path . PHP_EOL
                . 'destination: ' . $new_path . PHP_EOL
            );
        }
        echo 'moved' . PHP_EOL;
    } catch (\League\Flysystem\FileExistsException $e) {
        migrateReplace($path, $new_path);
    }
}

function migrateReplace($path, $new_path)
{
    /** @var \League\Flysystem\FilesystemInterface $files */
    global $filesystem;

    echo "Destination file already exist. What I need to do with duplicates?" . PHP_EOL;
    echo '     source: ' . $path . PHP_EOL;
    echo 'destination: ' . $new_path . PHP_EOL;
    echo '- "r": Replace the destination file with the source file' . PHP_EOL;
    echo '- "d": Delete the source file' . PHP_EOL;
    echo '- "s": Do nothing, just skip' . PHP_EOL;
    echo '- "n": Abort' . PHP_EOL;
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    $answer = trim($line);
    switch ($answer)
    {
        case 'r':
            $filesystem->delete($new_path);
            $filesystem->rename($path, $new_path);
            echo 'destination file replaced' . PHP_EOL;

            return true;
        case 'd':
            $filesystem->delete($path);
            echo 'source file deleted' . PHP_EOL;

            return true;
        case 's':
            echo 'skipped' . PHP_EOL;

            return true;
        default:
            echo "ABORTING!\n";
    }
    fclose($handle);
    exit;
}

function removeEmptyDirectories()
{
    $command = 'find ' . env('DATA_DIR') . ' -type d -empty -delete';
    echo "Delete all empty directories recursively? [y/n]:" . PHP_EOL;
    echo '  This command will be executed: ' . PHP_EOL
        . '  ' . $command . PHP_EOL;
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (trim($line) != 'y') {
        echo "ABORTING!\n";
        fclose($handle);
        exit;
    }
    fclose($handle);
    echo shell_exec($command);
}

array_walk($files, 'migrateMp3', ['extension' => 'mp3']);
array_walk($files, 'migrateImage', ['extension' => 'jpg']);
array_walk($files, 'migrateImage', ['extension' => 'png']);
array_walk($files, 'migrateImage', ['extension' => 'gif']);
removeEmptyDirectories();

echo 'Finish' . PHP_EOL;