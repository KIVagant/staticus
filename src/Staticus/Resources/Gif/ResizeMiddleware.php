<?php
namespace Staticus\Resources\Gif;

use League\Flysystem\FilesystemInterface;
use Staticus\Resources\Middlewares\Image\ImageResizeMiddlewareAbstract;

class ResizeMiddleware extends ImageResizeMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO, FilesystemInterface $filesystem)
    {
        parent::__construct($resourceDO, $filesystem);
    }
}
