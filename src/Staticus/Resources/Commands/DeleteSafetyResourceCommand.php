<?php
namespace Staticus\Resources\Commands;

use League\Flysystem\FilesystemInterface;
use Staticus\Resources\Exceptions\CommandErrorException;
use Staticus\Resources\ResourceDOInterface;

class DeleteSafetyResourceCommand implements ResourceCommandInterface
{
    use ShellFindCommandTrait;
    /**
     * @var ResourceDOInterface
     */
    protected $resourceDO;
    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    public function __construct(ResourceDOInterface $resourceDO, FilesystemInterface $filesystem)
    {
        $this->resourceDO = $resourceDO;
        $this->filesystem = $filesystem;
    }

    public function __invoke()
    {
        $uuid = $this->resourceDO->getUuid();
        $type = $this->resourceDO->getType();
        $variant = $this->resourceDO->getVariant();
        $version = $this->resourceDO->getVersion();
        $baseDir = $this->resourceDO->getBaseDirectory();
        $filePath = $this->resourceDO->getFilePath();
        if (!$uuid || !$type || !$baseDir) {
            throw new CommandErrorException('Invalid delete request', __LINE__);
        }
        if (is_file($filePath)) {
            // Make backup of the default version
            if (ResourceDOInterface::DEFAULT_VERSION === $version) {
                $lastVersion = $this->findLastExistsVersion($baseDir, $uuid, $type, $variant);

                // But only if previous existing version is not the default and not has the same content as deleting
                if (ResourceDOInterface::DEFAULT_VERSION !== $lastVersion) {
                    $lastVersionResourceDO = clone $this->resourceDO;
                    $lastVersionResourceDO->setVersion($lastVersion);
                    $command = new DestroyEqualResourceCommand($lastVersionResourceDO, $this->resourceDO, $this->filesystem);
                    $result = $command();
                    if ($result === $this->resourceDO) {

                        // If the previous file version already the same, current version is already deleted
                        // and backup and yet another deletion is not needed anymore
                        return $this->resourceDO;
                    }
                }

                $command = new BackupResourceCommand($this->resourceDO, $this->filesystem);
                $command($lastVersion);
            }

            $this->deleteFile($filePath);

            return $this->resourceDO;
        }

        return $this->resourceDO;
    }

    /**
     * @param $filePath
     */
    protected function deleteFile($filePath)
    {
        if (!unlink($filePath)) {
            throw new CommandErrorException('The file cannot be removed: ' . $filePath, __LINE__);
        }
    }
}