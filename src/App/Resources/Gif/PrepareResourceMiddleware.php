<?php
namespace App\Resources\Gif;

use App\Resources\PrepareResourceMiddlewareAbstract;
use App\Resources\ResourceImageDO;
use Common\Config\Config;

class PrepareResourceMiddleware extends PrepareResourceMiddlewareAbstract
{
    public function __construct(ResourceImageDO $resourceDO, Config $config)
    {
        parent::__construct($resourceDO, $config);
    }
}
