<?php
namespace App\Actions\Image;

use League\Flysystem\FilesystemInterface;
use Staticus\Middlewares\ActionListAbstract;
use Staticus\Resources\Image\ResourceImageDO;
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

    protected function allowedProperties()
    {
        $allowed = parent::allowedProperties();
        $allowed[] = ResourceImageDO::TOKEN_DIMENSION;

        return $allowed;
    }
}