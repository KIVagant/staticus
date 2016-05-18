<?php
namespace Staticus\Resources\File;

use Staticus\Resources\Middlewares\PrepareResourceMiddlewareAbstract;
use Staticus\Config\ConfigInterface;

class PrepareResourceMiddleware extends PrepareResourceMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO, ConfigInterface $config)
    {
        parent::__construct($resourceDO, $config);
    }
    protected function fillSpecificResourceSpecific() {}
}
