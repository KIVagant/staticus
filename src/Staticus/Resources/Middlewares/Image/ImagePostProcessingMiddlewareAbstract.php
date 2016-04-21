<?php
namespace Staticus\Resources\Middlewares\Image;

use Staticus\Diactoros\FileContentResponse\ResourceDoResponse;
use Staticus\Middlewares\MiddlewareAbstract;
use Staticus\Resources\Exceptions\SaveResourceErrorException;
use Staticus\Resources\Image\ResourceImageDOInterface;
use Staticus\Resources\ResourceDOInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\EmptyResponse;

abstract class ImagePostProcessingMiddlewareAbstract extends MiddlewareAbstract
{
    /**
     * @var ResourceImageDOInterface
     */
    protected $resourceDO;

    public function __construct(ResourceDOInterface $resourceDO)
    {
        $this->resourceDO = $resourceDO;
    }

    /**
     * @param ResponseInterface $response
     * @return bool
     */
    protected function isSupportedResponse(ResponseInterface $response)
    {
        return $response instanceof EmptyResponse
        || $response instanceof ResourceDoResponse;
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

    protected function getImagick($sourcePath)
    {
        $imagick = new \Imagick(realpath($sourcePath));

        return $imagick;
    }
}