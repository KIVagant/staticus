<?php
namespace Staticus\Resources\Mpeg;

use Staticus\Resources\SaveResourceMiddlewareAbstract;

class SaveResourceMiddleware extends SaveResourceMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO)
    {
        parent::__construct($resourceDO);
    }
}