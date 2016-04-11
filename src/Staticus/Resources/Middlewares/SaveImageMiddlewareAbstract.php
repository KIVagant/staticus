<?php
namespace Staticus\Resources\Middlewares;

use Staticus\Resources\File\ResourceDO;
use Staticus\Resources\ResourceDOInterface;
use Staticus\Resources\ResourceImageDO;

abstract class SaveImageMiddlewareAbstract extends SaveResourceMiddlewareAbstract
{
    protected function copyFileToDefaults(ResourceDOInterface $resourceDO)
    {
        /** @var ResourceImageDO $resourceDO */
        if (ResourceDO::DEFAULT_VARIANT !== $resourceDO->getVariant()) {
            $defaultDO = clone $resourceDO;
            $defaultDO->setVariant();
            $defaultDO->setVersion();
            $defaultDO->setWidth();
            $defaultDO->setHeight();
            $this->copyResource($resourceDO, $defaultDO);
        }
        if (ResourceDO::DEFAULT_VERSION !== $resourceDO->getVersion()) {
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
    protected function afterSave(ResourceDOInterface $resourceDO)
    {
        // If the basic version replaced
        if (ResourceImageDO::DEFAULT_SIZE === $resourceDO->getSize()) {
            $command = 'find '
                . $resourceDO->getBaseDirectory() . $resourceDO->getType()
                . DIRECTORY_SEPARATOR . $resourceDO->getVariant() . DIRECTORY_SEPARATOR
                . $resourceDO->getVersion() . DIRECTORY_SEPARATOR
                . '*x*' . DIRECTORY_SEPARATOR // only non-zero versions
                . ' -type f -name ' . $resourceDO->getUuid() . '.' . $resourceDO->getType();
            $command .= ' -delete';

            shell_exec($command);
        }
    }
    protected function backup(ResourceDOInterface $resourceDO)
    {

        return ResourceImageDO::DEFAULT_SIZE === $resourceDO->getSize()
            ? parent::backup($resourceDO)
            : $resourceDO;
    }
    protected function destroyEqual(ResourceDOInterface $resourceDO, ResourceDOInterface $backupResourceVerDO)
    {
        return ResourceImageDO::DEFAULT_SIZE === $resourceDO->getSize()
            ? parent::destroyEqual($resourceDO, $backupResourceVerDO)
            : $resourceDO;
    }
}
