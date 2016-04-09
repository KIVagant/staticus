<?php
namespace Staticus\Action\Fractal;

use App\Middlewares\ActionPostAbstract;
use App\Resources\ResourceDOInterface;
use App\Resources\ResourceImageDOInterface;
use FractalManager\Manager;

class ActionPost extends ActionPostAbstract
{
    public function __construct(ResourceImageDOInterface $resourceDO, Manager $manager)
    {
        $this->resourceDO = $resourceDO;
        $this->generator = $manager;
    }

    /**
     * @param ResourceDOInterface $resourceDO
     * @param $filePath
     * @return mixed
     */
    protected function generate(ResourceDOInterface $resourceDO, $filePath)
    {
        $content = $this->generator->generate($resourceDO->getName() . ' ' . $resourceDO->getNameAlternative());

        return $content;
    }
}