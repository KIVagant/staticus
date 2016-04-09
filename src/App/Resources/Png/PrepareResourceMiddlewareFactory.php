<?php
namespace App\Resources\Png;

use App\Resources\ResourceImageDO;
use App\Config\Config;

class PrepareResourceMiddlewareFactory
{
    private $resourceDO;
    /**
     * @var Config
     */
    private $config;

    /**
     * Final resource type is really important here! Be carefull with using ResourceDOInterface!
     * @param ResourceFileDO $resourceDO
     * @param Config $config
     */
    public function __construct(ResourceImageDO $resourceDO, Config $config)
    {
        $this->resourceDO = $resourceDO;
        $this->config = $config;
    }

    public function __invoke()
    {
        return new PrepareResourceMiddleware($this->resourceDO, $this->config);
    }
}
