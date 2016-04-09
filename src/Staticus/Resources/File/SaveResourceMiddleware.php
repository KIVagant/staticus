<?php
namespace Staticus\Resources\File;

use Staticus\Resources\Middlewares\SaveResourceMiddlewareAbstract;

class SaveResourceMiddleware extends SaveResourceMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO)
    {
        parent::__construct($resourceDO);
    }
}