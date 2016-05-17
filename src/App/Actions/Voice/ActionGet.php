<?php
namespace App\Actions\Voice;

use League\Flysystem\FilesystemInterface;
use Staticus\Middlewares\ActionGetAbstract;
use Staticus\Resources\Mpeg\ResourceDO;

class ActionGet extends ActionGetAbstract
{
    public function __construct(ResourceDO $resourceDO, FilesystemInterface $filesystem)
    {
        parent::__construct($resourceDO, $filesystem);
    }
}