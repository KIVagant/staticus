<?php
namespace Staticus\Resources\Gif;

use Staticus\Resources\Middlewares\ImagePostProcessingMiddlewareAbstract;

class PostProcessingMiddleware extends ImagePostProcessingMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO)
    {
        parent::__construct($resourceDO);
    }
}
