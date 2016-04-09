<?php
namespace Staticus\Action\Fractal;

use App\Middlewares\ActionGetAbstract;
use App\Resources\ResourceImageDOInterface;

class ActionGet extends ActionGetAbstract
{
    public function __construct(ResourceImageDOInterface $resourceDO)
    {
        $this->resourceDO = $resourceDO;
    }
}