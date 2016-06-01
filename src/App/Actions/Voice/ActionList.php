<?php
namespace App\Actions\Voice;

use League\Flysystem\FilesystemInterface;
use Staticus\Middlewares\ActionListAbstract;
use Staticus\Resources\Mpeg\ResourceDO;

class ActionList extends ActionListAbstract
{
    public function __construct(ResourceDO $resourceDO, FilesystemInterface $filesystem)
    {
        parent::__construct($resourceDO, $filesystem);
    }
}