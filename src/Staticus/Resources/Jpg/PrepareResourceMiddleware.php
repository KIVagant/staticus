<?php
namespace Staticus\Resources\Jpg;

use Staticus\Resources\Middlewares\Image\PrepareImageMiddlewareAbstract;
use Staticus\Config\ConfigInterface;

class PrepareResourceMiddleware extends PrepareImageMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO, ConfigInterface $config)
    {
        parent::__construct($resourceDO, $config);
    }
}
