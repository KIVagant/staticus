<?php
namespace App\Actions\Image;

use League\Flysystem\FilesystemInterface;
use SearchManager\Manager as SearchManager;
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
        , SearchManager $generatorSearch
    )
    {
        parent::__construct($resourceDO, $filesystem, $fractal, $generatorSearch);
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
    protected function search(ResourceDOInterface $resourceDO)
    {
        $query = $resourceDO->getName() . ' ' . $resourceDO->getNameAlternative();
        $content = $this->searcher->generate($query);

        return $content;
    }
}