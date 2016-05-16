<?php
namespace Staticus\Resources\Commands;

use League\Flysystem\FilesystemInterface;
use Staticus\Resources\Exceptions\CommandErrorException;
use Staticus\Resources\ResourceDOInterface;

class DestroyResourceCommand implements ResourceCommandInterface
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

    /**
     * @param bool $byPathOnly If true, no search on disk will be executed
     * @return ResourceDOInterface
     */
    public function __invoke($byPathOnly = false)
    {
        $uuid = $this->resourceDO->getUuid();
        $type = $this->resourceDO->getType();
        $variant = $this->resourceDO->getVariant();
        $version = $this->resourceDO->getVersion();
        $baseDir = $this->resourceDO->getBaseDirectory();
        $filePath = $this->resourceDO->getFilePath();
        if (!$uuid || !$type || !$baseDir || !$filePath) {
            throw new CommandErrorException('Invalid destroy request', __LINE__);
        }
        if ($byPathOnly) {
            if (!$this->filesystem->delete($filePath)) {
                throw new CommandErrorException('The file cannot be removed: ' . $filePath, __LINE__);
            }
        } else {
            $command = $this->getShellFindCommand($baseDir, $uuid, $type, $variant, $version);
            $command .= ' -delete';
            shell_exec($command . '> /dev/null 2>&1');
        }

        return $this->resourceDO;
    }
}