<?php
namespace App\Actions\Image;

use League\Flysystem\FilesystemInterface;
use Staticus\Middlewares\ActionGetAbstract;
use Staticus\Resources\Image\ResourceImageDOInterface;

class ActionGet extends ActionGetAbstract
{
    public function __construct(ResourceImageDOInterface $resourceDO, FilesystemInterface $filesystem)
    {
        parent::__construct($resourceDO, $filesystem);
    }
}