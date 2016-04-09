<?php
namespace App\Resources\File;

use App\Resources\PrepareResourceMiddlewareAbstract;
use App\Config\Config;

class PrepareResourceMiddleware extends PrepareResourceMiddlewareAbstract
{
    public function __construct(ResourceFileDO $resourceDO, Config $config)
    {
        parent::__construct($resourceDO, $config);
    }
}
