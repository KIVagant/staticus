<?php
namespace Staticus\Resources\Jpg;

use League\Flysystem\FilesystemInterface;
use Staticus\Config\ConfigInterface;
use Staticus\Resources\Middlewares\Image\ImageStripMiddlewareAbstract;

class StripMiddleware extends ImageStripMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO, FilesystemInterface $filesystem, ConfigInterface $config)
    {
        parent::__construct($resourceDO, $filesystem, $config);
    }
}
