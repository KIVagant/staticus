<?php
namespace Staticus\Resources\Jpg;

use Staticus\Resources\Middlewares\Image\ImageCropMiddlewareAbstract;

class CropMiddleware extends ImageCropMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO)
    {
        parent::__construct($resourceDO);
    }
}
