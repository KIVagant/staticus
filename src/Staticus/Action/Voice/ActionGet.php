<?php
namespace Staticus\Action\Voice;

use App\Middlewares\ActionGetAbstract;
use App\Resources\Mpeg\ResourceDO;

class ActionGet extends ActionGetAbstract
{
    public function __construct(ResourceDO $resourceDO)
    {
        $this->resourceDO = $resourceDO;
    }
}