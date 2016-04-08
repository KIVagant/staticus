<?php
namespace App\Resources\Jpg;

use App\Resources\ResourceImageDO;

class SaveResourceMiddlewareFactory
{
    private $resourceDO;

    public function __construct(ResourceImageDO $resourceDO)
    {
        $this->resourceDO = $resourceDO;
    }

    public function __invoke()
    {
        return new SaveResourceMiddleware($this->resourceDO);
    }
}
