<?php
namespace Staticus\Resource;

class PrepareResourceMiddlewareFactory
{
    private $resourceDO;

    public function __construct(ResourceDO $resourceDO)
    {
        $this->resourceDO = $resourceDO;
    }

    public function __invoke()
    {
        return new PrepareResourceMiddleware($this->resourceDO);
    }
}
