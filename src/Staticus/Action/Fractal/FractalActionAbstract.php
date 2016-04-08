<?php
namespace Staticus\Action\Fractal;

use Common\Config\Config;
use FractalManager\Manager;
use Staticus\Action\StaticMiddlewareAbstract;
use Staticus\Resource\ResourceDO;

abstract class FractalActionAbstract extends StaticMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO, Manager $manager, Config $config)
    {
        $this->resourceDO = $resourceDO;
        $this->generator = $manager;
        $this->providerName = $this->getRealClassName($this->generator->getAdapter());
        $this->config = $config->get('fractal');
    }

    /**
     * @param $text
     * @param $filePath
     * @return mixed
     */
    protected function generate($text, $filePath)
    {
        $content = $this->generator->generate($text);

        return $content;
    }
}