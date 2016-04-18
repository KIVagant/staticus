<?php
namespace Staticus\Resources\Png;

use Staticus\Resources\ImagePostProcessingMiddlewareAbstract;

class PostProcessingMiddleware extends ImagePostProcessingMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO)
    {
        parent::__construct($resourceDO);
    }
}
