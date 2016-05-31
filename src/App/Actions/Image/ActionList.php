<?php
namespace App\Actions\Image;

use League\Flysystem\FilesystemInterface;
use Staticus\Middlewares\ActionListAbstract;
use Staticus\Resources\Commands\FindImageSizesResourceCommand;
use Staticus\Resources\Image\ResourceImageDOInterface;

class ActionList extends ActionListAbstract
{
    public function __construct(
        ResourceImageDOInterface $resourceDO
        , FilesystemInterface $filesystem
    )
    {
        parent::__construct($resourceDO, $filesystem);
    }

    public function action()
    {
        parent::action();
        $sizes = [];
        foreach ($this->actionResult['versions'] as $version) {
            $resourceDO = clone $this->resourceDO;
            $resourceDO->setVersion($version);
            $resourceDO->setWidth();
            $resourceDO->setHeight();
            // JSON will remove string '0' keys, so add the prefix
            $sizes['v' . $version] = $this->findSizes($resourceDO);
        }
        $this->actionResult['sizes'] = $sizes;
    }
    protected function findSizes(ResourceImageDOInterface $resourceDO)
    {
        $command = new FindImageSizesResourceCommand($resourceDO, $this->filesystem);

        return $command();
    }
}