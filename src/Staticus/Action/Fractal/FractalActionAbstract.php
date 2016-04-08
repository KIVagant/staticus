<?php
namespace Staticus\Action\Fractal;

use App\Resources\ResourceDOInterface;
use App\Resources\ResourceImageDO;
use Common\Config\Config;
use FractalManager\Manager;
use Staticus\Action\StaticMiddlewareAbstract;

abstract class FractalActionAbstract extends StaticMiddlewareAbstract
{
    public function __construct(ResourceImageDO $resourceDO, Manager $manager, Config $config)
    {
        $this->resourceDO = $resourceDO;
        $this->generator = $manager;
        $this->providerName = $this->getRealClassName($this->generator->getAdapter());
        $this->config = $config;
    }

    /**
     * @param ResourceDOInterface $resourceDO
     * @param $filePath
     * @return mixed
     * @internal param $text
     */
    protected function generate(ResourceDOInterface $resourceDO, $filePath)
    {
        $content = $this->generator->generate($resourceDO->getName() . ' ' . $resourceDO->getNameAlternative());

        return $content;
    }
}