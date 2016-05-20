<?php
namespace App\Actions\Image;

use SearchManager\Manager as SearchManager;
use Staticus\Middlewares\ActionSearchAbstract;
use Staticus\Resources\ResourceDOInterface;
use Staticus\Resources\Image\ResourceImageDOInterface;

class ActionSearchJpg extends ActionSearchAbstract
{
    public function __construct(
        ResourceImageDOInterface $resourceDO
        , SearchManager $manager
    )
    {
        parent::__construct($resourceDO, $manager);
    }

    protected function search(ResourceDOInterface $resourceDO)
    {
        /** @var SearchManager $searcher */
        $searcher = $this->searcher;
        $query = $resourceDO->getName() . ' ' . $resourceDO->getNameAlternative();

        return $searcher->generate($query);
    }
}