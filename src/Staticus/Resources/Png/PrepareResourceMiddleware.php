<?php
namespace Staticus\Resources\Png;

use Staticus\Resources\Middlewares\PrepareResourceMiddlewareAbstract;
use Staticus\Config\Config;

class PrepareResourceMiddleware extends PrepareResourceMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO, Config $config)
    {
        parent::__construct($resourceDO, $config);
    }
}
