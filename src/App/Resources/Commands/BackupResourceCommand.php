<?php
namespace App\Resources\Commands;

use App\Resources\ResourceDOInterface;

class BackupResourceCommand implements ResourceCommandInterface
{
    use ShellFindCommandTrait;
    /**
     * @var ResourceDOInterface
     */
    protected $resourceDO;

    public function __construct(ResourceDOInterface $resourceDO)
    {
        $this->resourceDO = $resourceDO;
    }
    public function run()
    {
        $uuid = $this->resourceDO->getUuid();
        $type = $this->resourceDO->getType();
        $variant = $this->resourceDO->getVariant();
        $baseDir = $this->resourceDO->getBaseDirectory();
        $lastVersion = $this->findLastExistsVersion($baseDir, $uuid, $type, $variant);

        return $this->backupResource($lastVersion + 1);
    }

    /**
     * @param $newVersion
     */
    protected function backupResource($newVersion)
    {
        $backupResourceDO = clone $this->resourceDO;
        $backupResourceDO->setVersion($newVersion);
        $command = new CopyResourceCommand($this->resourceDO, $backupResourceDO);

        return $command->run();
    }
}