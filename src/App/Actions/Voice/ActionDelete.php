<?php
namespace App\Actions\Voice;

use League\Flysystem\FilesystemInterface;
use Staticus\Middlewares\ActionDeleteAbstract;
use Staticus\Resources\Mpeg\ResourceDO;

class ActionDelete extends ActionDeleteAbstract
{
    public function __construct(ResourceDO $resourceDO, FilesystemInterface $filesystem)
    {
        parent::__construct($resourceDO, $filesystem);
    }
}