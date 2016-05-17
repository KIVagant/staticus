<?php
namespace Staticus\Resources\Mpeg;

use League\Flysystem\FilesystemInterface;
use Staticus\Resources\Middlewares\SaveResourceMiddlewareAbstract;
use Staticus\Resources\ResourceDOInterface;

class SaveResourceMiddleware extends SaveResourceMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO, FilesystemInterface $filesystem)
    {
        parent::__construct($resourceDO, $filesystem);
    }
    protected function afterSave(ResourceDOInterface $resourceDO) {}
}