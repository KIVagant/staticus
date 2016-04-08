<?php
namespace App\Resources;

use Staticus\Resource\ResourceDO;

class SavePngMiddlewareFactory
{
    private $resourceDO;

    public function __construct(ResourceDO $resourceDO)
    {
        $this->resourceDO = $resourceDO;
    }

    public function __invoke()
    {
        return new SavePngMiddleware($this->resourceDO);
    }
}
