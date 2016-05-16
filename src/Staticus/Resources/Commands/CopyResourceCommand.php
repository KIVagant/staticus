<?php

namespace Staticus\Resources\Commands;

use League\Flysystem\FilesystemInterface;
use Staticus\Resources\Exceptions\CommandErrorException;
use Staticus\Resources\ResourceDOInterface;

class CopyResourceCommand implements ResourceCommandInterface
{
    /**
     * @var ResourceDOInterface
     */
    protected $originResourceDO;
    /**
     * @var ResourceDOInterface
     */
    protected $newResourceDO;
    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    public function __construct(ResourceDOInterface $originResourceDO, ResourceDOInterface $newResourceDO, FilesystemInterface $filesystem)
    {
        $this->originResourceDO = $originResourceDO;
        $this->newResourceDO = $newResourceDO;
        $this->filesystem = $filesystem;
    }
    public function __invoke()
    {
        $originPath = $this->originResourceDO->getFilePath();
        $newPath = $this->newResourceDO->getFilePath();
        if (!$this->filesystem->has($originPath)) {
            throw new CommandErrorException('Origin file is not exists: ' . $originPath, __LINE__);
        }
        if (!$this->filesystem->copy($originPath, $newPath)) {
            $this->copyFile($originPath, $newPath);

            return $this->newResourceDO;
        }

        return $this->originResourceDO;
    }

    protected function copyFile($fromFullPath, $toFullPath)
    {
        $this->createDirectory(dirname($toFullPath));
        if (!$this->filesystem->copy($fromFullPath, $toFullPath)) {
            throw new CommandErrorException('File cannot be copied to the default path ' . $toFullPath, __LINE__);
        }
    }

    protected function createDirectory($directory)
    {
        if (!$this->filesystem->createDir($directory)) {
            throw new CommandErrorException('Can\'t create a directory: ' . $directory, __LINE__);
        }
    }
}