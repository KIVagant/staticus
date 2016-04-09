<?php
namespace App\Resources\Commands;

use App\Resources\ResourceDOInterface;

class DestroyEqualResourceCommand implements ResourceCommandInterface
{
    /**
     * @var ResourceDOInterface
     */
    protected $originResourceDO;
    /**
     * @var ResourceDOInterface
     */
    private $suspectResourceDO;

    /**
     * @param ResourceDOInterface $originResourceDO
     * @param ResourceDOInterface $suspectResourceDO This resource will be deleted, if equal to $originResourceDO
     */
    public function __construct(ResourceDOInterface $originResourceDO, ResourceDOInterface $suspectResourceDO)
    {
        $this->originResourceDO = $originResourceDO;
        $this->suspectResourceDO = $suspectResourceDO;
    }

    /**
     * @return ResourceDOInterface
     */
    public function run()
    {
        $originType = $this->originResourceDO->getType();
        $suspectType = $this->suspectResourceDO->getType();
        $originFilePath = $this->originResourceDO->getFilePath();
        $suspectFilePath = $this->suspectResourceDO->getFilePath();
        if ($originType === $suspectType
            && filesize($originFilePath) === filesize($suspectFilePath)
            && md5_file($originFilePath) === md5_file($suspectFilePath)
        ) {
            $command = new DestroyResourceCommand($this->suspectResourceDO);

            return $command->run(true);
        }

        return $this->originResourceDO;
    }
}