<?php
namespace Staticus\Action\Fractal;

use App\Resources\ResourceDOInterface;
use App\Resources\ResourceImageDO;
use FractalManager\Manager;
use App\Middlewares\ActionMiddlewareAbstract;

abstract class FractalActionAbstract extends ActionMiddlewareAbstract
{
    public function __construct(ResourceImageDO $resourceDO, Manager $manager)
    {
        $this->resourceDO = $resourceDO;
        $this->generator = $manager;
        $this->providerName = $this->getRealClassName($this->generator->getAdapter());
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