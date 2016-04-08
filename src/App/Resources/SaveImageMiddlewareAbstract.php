<?php
namespace App\Resources;

use App\Resources\File\ResourceFileDO;

abstract class SaveImageMiddlewareAbstract extends SaveResourceMiddlewareAbstract
{
    public function __construct(ResourceImageDO $resourceDO)
    {
        parent::__construct($resourceDO);
    }
    protected function copyFileToDefaults(ResourceImageDO $resourceDO)
    {
        if (ResourceFileDO::DEFAULT_VARIANT !== $resourceDO->getVariant()) {
            $defaultDO = clone $resourceDO;
            $defaultDO->setVariant();
            $defaultDO->setVersion();
            $defaultDO->setWidth();
            $defaultDO->setHeight();
            $this->copyResource($resourceDO, $defaultDO);
        }
        if (ResourceFileDO::DEFAULT_VERSION !== $resourceDO->getVersion()) {
            $defaultDO = clone $resourceDO;
            $defaultDO->setVersion();
            $defaultDO->setWidth();
            $defaultDO->setHeight();
            $this->copyResource($resourceDO, $defaultDO);
        }
        if (ResourceImageDO::DEFAULT_SIZE !== $resourceDO->getSize()) {
            $defaultDO = clone $resourceDO;
            $defaultDO->setWidth();
            $defaultDO->setHeight();
            $this->copyResource($resourceDO, $defaultDO);
        }
    }
}
