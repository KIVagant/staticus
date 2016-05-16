<?php
namespace Staticus\Resources\Gif;

use Staticus\Resources\Middlewares\Image\ImageCropMiddlewareAbstract;
use League\Flysystem\FilesystemInterface;

class CropMiddleware extends ImageCropMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO, FilesystemInterface $filesystem)
    {
        parent::__construct($resourceDO, $filesystem);
    }
}
