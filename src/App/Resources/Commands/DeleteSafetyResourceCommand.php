<?php
namespace App\Resources\Commands;

use App\Resources\Exceptions\CommandErrorException;
use App\Resources\ResourceDOInterface;

class DeleteSafetyResourceCommand implements ResourceCommandInterface
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
        $version = $this->resourceDO->getVersion();
        $baseDir = $this->resourceDO->getBaseDirectory();
        $filePath = $this->resourceDO->getFilePath();
        if (!$uuid || !$type || !$baseDir) {
            throw new CommandErrorException('Invalid delete request');
        }
        if (file_exists($filePath)) {
            // Если не запрошено удаление конкретной версии — делаем бекап
            if (ResourceDOInterface::DEFAULT_VERSION === $version) {
                $command = new BackupResourceCommand($this->resourceDO);
                $command->run();
            }

            if ($this->deleteFile($filePath)) {

                return $this->resourceDO;
            }
        }

        return self::NOT_EXISTS;
    }

    /**
     * @param $filePath
     */
    protected function deleteFile($filePath)
    {
        if (!unlink($filePath)) {
            throw new CommandErrorException('The file cannot be removed: ' . $filePath);
        }

        return self::SUCCESS;
    }
}