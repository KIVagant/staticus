<?php
namespace Staticus\Action\Fractal;

use App\Middlewares\ActionDeleteAbstract;
use App\Resources\ResourceImageDOInterface;

class ActionDelete extends ActionDeleteAbstract
{
    public function __construct(ResourceImageDOInterface $resourceDO)
    {
        $this->resourceDO = $resourceDO;
    }
}