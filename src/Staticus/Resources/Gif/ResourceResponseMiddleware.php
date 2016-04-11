<?php
namespace Staticus\Resources\Gif;

use Staticus\Resources\Middlewares\ImageResponseMiddlewareAbstract;

class ResourceResponseMiddleware extends ImageResponseMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO)
    {
        parent::__construct($resourceDO);
    }
}
