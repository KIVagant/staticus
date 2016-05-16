<?php
namespace Staticus\Resources\Jpg;

use League\Flysystem\FilesystemInterface;
use Staticus\Resources\Middlewares\Image\ImageCropMiddlewareAbstract;

class CropMiddleware extends ImageCropMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO, FilesystemInterface $filesystem)
    {
        parent::__construct($resourceDO, $filesystem);
    }
}
