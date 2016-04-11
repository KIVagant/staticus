<?php
namespace Staticus\Resources\File;

use Staticus\Resources\Middlewares\ResourceResponseMiddlewareAbstract;

class ResourceResponseMiddleware extends ResourceResponseMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO)
    {
        parent::__construct($resourceDO);
    }
}
