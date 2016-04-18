<?php
namespace Staticus\Resources\Middlewares;

use Staticus\Middlewares\MiddlewareAbstract;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Staticus\Resources\Exceptions\SaveResourceErrorException;
use Staticus\Resources\ResourceDOInterface;

abstract class ImagePostProcessingMiddlewareAbstract extends MiddlewareAbstract
{
    protected $resourceDO;
    public function __construct(ResourceDOInterface $resourceDO)
    {
        $this->resourceDO = $resourceDO;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    )
    {
        parent::__invoke($request, $response, $next);

        $resourceDO = $this->resourceDO;
        if ($resourceDO->getSize()) {
            if (!is_file($resourceDO->getFilePath())) {
                $defaultSizeResourceDO = clone $resourceDO;
                $defaultSizeResourceDO->setWidth();
                $defaultSizeResourceDO->setHeight();
                if (is_file($defaultSizeResourceDO->getFilePath())) {
                    $this->resizeImage($defaultSizeResourceDO->getFilePath(), $resourceDO->getFilePath(), $resourceDO->getWidth(), $resourceDO->getHeight());
                }
            }
        }

        return $next($request, $response);
    }
    public function resizeImage($sourcePath, $destinationPath, $width, $height)
    {
        $this->createDirectory(dirname($destinationPath));
        $imagick = new \Imagick(realpath($sourcePath));
        $imagick->adaptiveResizeImage($width, $height, true);
        $resource = fopen($destinationPath, "w");
        if (!$resource) {
            throw new SaveResourceErrorException('Can\'t open file for write: ' . $destinationPath, __LINE__);
        }
        $imagick->writeImageFile($resource);
        fclose($resource);
    }
    /**
     * @param $directory
     * @throws SaveResourceErrorException
     * @deprecated
     * @todo move file operations somewhere
     * @see \Staticus\Resources\Middlewares\SaveResourceMiddlewareAbstract::createDirectory
     */
    protected function createDirectory($directory)
    {
        if (@!mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new SaveResourceErrorException('Can\'t create a directory: ' . $directory, __LINE__);
        }
    }
}
