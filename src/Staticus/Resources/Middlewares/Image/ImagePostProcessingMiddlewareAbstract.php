<?php
namespace Staticus\Resources\Middlewares\Image;

use League\Flysystem\FilesystemInterface;
use Staticus\Diactoros\FileContentResponse\ResourceDoResponse;
use Staticus\Middlewares\MiddlewareAbstract;
use Staticus\Resources\Exceptions\SaveResourceErrorException;
use Staticus\Resources\Image\ResourceImageDOInterface;
use Staticus\Resources\ResourceDOInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Stratigility\Http\Response;

abstract class ImagePostProcessingMiddlewareAbstract extends MiddlewareAbstract
{
    /**
     * @var ResourceImageDOInterface
     */
    protected $resourceDO;
    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    public function __construct(ResourceDOInterface $resourceDO, FilesystemInterface $filesystem)
    {
        $this->resourceDO = $resourceDO;
        $this->filesystem = $filesystem;
    }

    /**
     * @param ResponseInterface $response
     * @return bool
     */
    protected function isSupportedResponse(ResponseInterface $response)
    {
        return $response instanceof EmptyResponse
        || $response instanceof ResourceDoResponse
        || $response instanceof Response;
    }

    protected function getTargetResourceDO()
    {
        $defaultSizeResourceDO = clone $this->resourceDO;
        $defaultSizeResourceDO->setWidth();
        $defaultSizeResourceDO->setHeight();

        return $defaultSizeResourceDO;
    }


    /**
     * @param ResponseInterface $response
     * @return ResourceDOInterface|ResourceImageDOInterface
     */
    protected function chooseTargetResource(ResponseInterface $response)
    {
        $targetResourceDO = ($response instanceof ResourceDoResponse)
            ? $response->getContent()
            : $this->getTargetResourceDO();
        return $targetResourceDO;
    }

    /**
     * @param $directory
     * @throws SaveResourceErrorException
     * @see \Staticus\Resources\Middlewares\SaveResourceMiddlewareAbstract::createDirectory
     */
    protected function createDirectory($directory)
    {
        if (!$this->filesystem->createDir($directory)) {
            throw new SaveResourceErrorException('Can\'t create a directory: ' . $directory, __LINE__);
        }
    }

    protected function getImagick($sourcePath)
    {
        if (!class_exists(\Imagick::class)) {
            throw new SaveResourceErrorException('Imagick is not installed', __LINE__);
        }
        $imagick = new \Imagick(realpath($sourcePath));

        return $imagick;
    }
}