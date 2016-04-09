<?php
namespace Staticus\Resources\Commands;

use Staticus\Resources\ResourceDOInterface;

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

    /**
     * @param null $lastVersion You can set the last existing version manually, if needed
     * @return ResourceDOInterface|int
     */
    public function __invoke($lastVersion = null)
    {
        if (null === $lastVersion) {
            $uuid = $this->resourceDO->getUuid();
            $type = $this->resourceDO->getType();
            $variant = $this->resourceDO->getVariant();
            $baseDir = $this->resourceDO->getBaseDirectory();
            $lastVersion = $this->findLastExistsVersion($baseDir, $uuid, $type, $variant);
        }

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

        return $command();
    }
}