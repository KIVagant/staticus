<?php
namespace Staticus\Resources\Png;

use League\Flysystem\FilesystemInterface;
use Staticus\Resources\Middlewares\Image\ImageStripMiddlewareAbstract;
use Zend\Session\Config\ConfigInterface;

class StripMiddleware extends ImageStripMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO, FilesystemInterface $filesystem, ConfigInterface $config)
    {
        parent::__construct($resourceDO, $filesystem, $config);
    }
}
