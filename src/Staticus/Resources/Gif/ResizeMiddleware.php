<?php
namespace Staticus\Resources\Gif;

use Staticus\Resources\Middlewares\ImageResizeMiddlewareAbstract;

class ResizeMiddleware extends ImageResizeMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO)
    {
        parent::__construct($resourceDO);
    }
}
