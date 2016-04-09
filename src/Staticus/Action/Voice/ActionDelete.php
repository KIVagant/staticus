<?php
namespace Staticus\Action\Voice;

use App\Middlewares\ActionDeleteAbstract;
use App\Resources\Mpeg\ResourceDO;

class ActionDelete extends ActionDeleteAbstract
{
    public function __construct(ResourceDO $resourceDO)
    {
        $this->resourceDO = $resourceDO;
    }
}