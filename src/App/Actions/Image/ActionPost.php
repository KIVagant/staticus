<?php
namespace App\Actions\Image;

use Staticus\Middlewares\ActionPostAbstract;
use Staticus\Resources\ResourceDOInterface;
use Staticus\Resources\ResourceImageDOInterface;
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