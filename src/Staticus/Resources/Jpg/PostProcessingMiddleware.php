<?php
namespace Staticus\Resources\Jpg;

use Staticus\Resources\ImagePostProcessingMiddlewareAbstract;

class PostProcessingMiddleware extends ImagePostProcessingMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO)
    {
        parent::__construct($resourceDO);
    }
}
