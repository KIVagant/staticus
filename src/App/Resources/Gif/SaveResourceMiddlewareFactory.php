<?php
namespace App\Resources\Gif;

class SaveResourceMiddlewareFactory
{
    private $resourceDO;

    public function __construct(ResourceDO $resourceDO)
    {
        $this->resourceDO = $resourceDO;
    }

    public function __invoke()
    {
        return new SaveResourceMiddleware($this->resourceDO);
    }
}
