<?php
namespace App\Actions\Fractal;

use Staticus\Middlewares\ActionGetAbstract;
use Staticus\Resources\ResourceImageDOInterface;

class ActionGet extends ActionGetAbstract
{
    public function __construct(ResourceImageDOInterface $resourceDO)
    {
        $this->resourceDO = $resourceDO;
    }
}