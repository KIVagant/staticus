<?php
namespace App\Resources\File;

use App\Resources\SaveResourceMiddlewareAbstract;

class SaveResourceMiddleware extends SaveResourceMiddlewareAbstract
{
    public function __construct(ResourceFileDO $resourceDO)
    {
        parent::__construct($resourceDO);
    }
}