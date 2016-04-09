<?php
namespace App\Resources\Mpeg;

use App\Resources\SaveResourceMiddlewareAbstract;

class SaveResourceMiddleware extends SaveResourceMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO)
    {
        parent::__construct($resourceDO);
    }
}