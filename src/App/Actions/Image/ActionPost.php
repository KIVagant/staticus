<?php
namespace App\Actions\Image;

use League\Flysystem\FilesystemInterface;
use Staticus\Middlewares\ActionPostAbstract;
use Staticus\Resources\ResourceDOInterface;
use Staticus\Resources\Image\ResourceImageDOInterface;
use FractalManager\Manager as FractalManager;

class ActionPost extends ActionPostAbstract
{
    public function __construct(
        ResourceImageDOInterface $resourceDO
        , FilesystemInterface $filesystem
        , FractalManager $fractal
    )
    {
        parent::__construct($resourceDO, $filesystem, $fractal);
    }

    /**
     * @param ResourceDOInterface $resourceDO
     * @return mixed
     */
    protected function generate(ResourceDOInterface $resourceDO)
    {
        $query = $resourceDO->getName() . ' ' . $resourceDO->getNameAlternative();
        $content = $this->generator->generate($query);

        return $content;
    }
}