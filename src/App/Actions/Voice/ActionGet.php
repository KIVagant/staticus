<?php
namespace App\Actions\Voice;

use Staticus\Middlewares\ActionGetAbstract;
use Staticus\Resources\Mpeg\ResourceDO;

class ActionGet extends ActionGetAbstract
{
    public function __construct(ResourceDO $resourceDO)
    {
        $this->resourceDO = $resourceDO;
    }
}