<?php
namespace App\Resources\Mpeg;

use App\Resources\PrepareResourceMiddlewareAbstract;
use App\Config\Config;

class PrepareResourceMiddleware extends PrepareResourceMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO, Config $config)
    {
        parent::__construct($resourceDO, $config);
    }
}
