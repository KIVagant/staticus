<?php
namespace Staticus\Resources\File;

use Staticus\Resources\Middlewares\SaveResourceMiddlewareAbstract;
use Staticus\Resources\ResourceDOInterface;

class SaveResourceMiddleware extends SaveResourceMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO)
    {
        parent::__construct($resourceDO);
    }
    protected function afterSave(ResourceDOInterface $resourceDO) {}
}