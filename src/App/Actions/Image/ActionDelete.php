<?php
namespace App\Actions\Image;

use League\Flysystem\FilesystemInterface;
use Staticus\Middlewares\ActionDeleteAbstract;
use Staticus\Resources\Image\ResourceImageDOInterface;

class ActionDelete extends ActionDeleteAbstract
{
    public function __construct(ResourceImageDOInterface $resourceDO, FilesystemInterface $filesystem)
    {
        parent::__construct($resourceDO, $filesystem);
    }
}