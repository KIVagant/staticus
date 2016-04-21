<?php
namespace App\Actions\Image;

use Staticus\Middlewares\ActionDeleteAbstract;
use Staticus\Resources\Image\ResourceImageDOInterface;

class ActionDelete extends ActionDeleteAbstract
{
    public function __construct(ResourceImageDOInterface $resourceDO)
    {
        $this->resourceDO = $resourceDO;
    }
}