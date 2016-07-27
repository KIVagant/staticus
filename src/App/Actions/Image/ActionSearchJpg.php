<?php
namespace App\Actions\Image;

use SearchManager\Manager as SearchManager;
use Staticus\Auth\UserInterface;
use Staticus\Config\ConfigInterface;
use Staticus\Middlewares\ActionSearchAbstract;
use Staticus\Resources\Image\ResourceImageDOInterface;

class ActionSearchJpg extends ActionSearchAbstract
{

    public function __construct(
        ResourceImageDOInterface $resourceDO
        , SearchManager $manager
        , UserInterface $user
        , ConfigInterface $config
    )
    {
        parent::__construct($resourceDO, $manager, $user, $config);
        $this->user = $user;
    }

    /**
     * @return \SearchManager\Image\SearchImageDTO
     */
    protected function search()
    {
        /** @var SearchManager $searcher */
        $searcher = $this->searcher;
        $query = $this->getQuery();
        $cursor = $this->getCursor();

        return $searcher->generate($query, $cursor);
    }

    /**
     * @return string
     */
    protected function getQuery()
    {
        return $this->resourceDO->getName() . ' ' . $this->resourceDO->getNameAlternative();
    }
}