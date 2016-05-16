<?php
namespace Staticus\Resources\Png;

use League\Flysystem\FilesystemInterface;
use Staticus\Resources\Middlewares\Image\ImageCropMiddlewareAbstract;

class CropMiddleware extends ImageCropMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO, FilesystemInterface $filesystem)
    {
        parent::__construct($resourceDO, $filesystem);
    }
}
