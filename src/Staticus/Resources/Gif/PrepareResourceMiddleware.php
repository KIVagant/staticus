<?php
namespace Staticus\Resources\Gif;

use Staticus\Resources\Middlewares\PrepareImageMiddlewareAbstract;
use Staticus\Config\Config;

class PrepareResourceMiddleware extends PrepareImageMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO, Config $config)
    {
        parent::__construct($resourceDO, $config);
    }
}
