<?php
namespace App\Actions\Fractal;

use Staticus\Middlewares\ActionDeleteAbstract;
use Staticus\Resources\ResourceImageDOInterface;

class ActionDelete extends ActionDeleteAbstract
{
    public function __construct(ResourceImageDOInterface $resourceDO)
    {
        $this->resourceDO = $resourceDO;
    }
}