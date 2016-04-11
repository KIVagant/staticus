<?php
namespace App\Actions\Image;

use Staticus\Middlewares\ActionGetAbstract;
use Staticus\Resources\ResourceImageDOInterface;

class ActionGet extends ActionGetAbstract
{
    public function __construct(ResourceImageDOInterface $resourceDO)
    {
        $this->resourceDO = $resourceDO;
    }
}