<?php
namespace Staticus\Resources\Jpg;

use Staticus\Resources\Middlewares\Image\PrepareImageMiddlewareAbstract;
use Staticus\Config\Config;

class PrepareResourceMiddleware extends PrepareImageMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO, Config $config)
    {
        parent::__construct($resourceDO, $config);
    }
}
