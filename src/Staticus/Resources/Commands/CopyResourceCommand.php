<?php

namespace Staticus\Resources\Commands;

use Staticus\Resources\Exceptions\CommandErrorException;
use Staticus\Resources\ResourceDOInterface;

class CopyResourceCommand implements ResourceCommandInterface
{
    /**
     * @var ResourceDOInterface
     */
    private $originResourceDO;
    /**
     * @var ResourceDOInterface
     */
    private $newResourceDO;

    public function __construct(ResourceDOInterface $originResourceDO, ResourceDOInterface $newResourceDO)
    {
        $this->originResourceDO = $originResourceDO;
        $this->newResourceDO = $newResourceDO;
    }
    public function __invoke()
    {
        $originPath = $this->originResourceDO->getFilePath();
        $newPath = $this->newResourceDO->getFilePath();
        if (!is_file($originPath)) {
            throw new CommandErrorException('Can\'t copy a file: ' . $originPath, __LINE__);
        }
        if (!is_file($newPath)) {
            $this->copyFile($originPath, $newPath);

            return $this->newResourceDO;
        }

        return $this->originResourceDO;
    }

    protected function copyFile($fromFullPath, $toFullPath)
    {
        $this->createDirectory(dirname($toFullPath));
        if (!copy($fromFullPath, $toFullPath)) {
            throw new CommandErrorException('File cannot be copied to the default path ' . $toFullPath, __LINE__);
        }
    }

    protected function createDirectory($directory)
    {
        if (@!mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new CommandErrorException('Can\'t create a directory: ' . $directory, __LINE__);
        }
    }
}