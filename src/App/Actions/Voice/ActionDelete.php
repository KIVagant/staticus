<?php
namespace App\Actions\Voice;

use Staticus\Middlewares\ActionDeleteAbstract;
use Staticus\Resources\Mpeg\ResourceDO;

class ActionDelete extends ActionDeleteAbstract
{
    public function __construct(ResourceDO $resourceDO)
    {
        $this->resourceDO = $resourceDO;
    }
}