<?php
namespace App\Actions\Image;

use League\Flysystem\FilesystemInterface;
use Staticus\Middlewares\ActionListAbstract;
use Staticus\Resources\Image\ResourceImageDO;
use Staticus\Resources\Image\ResourceImageDOInterface;

class ActionList extends ActionListAbstract
{
    public function __construct(
        ResourceImageDOInterface $resourceDO
        , FilesystemInterface $filesystem
    )
    {
        parent::__construct($resourceDO, $filesystem);
    }

    protected function allowedProperties()
    {
        $allowed = parent::allowedProperties();
        $allowed[] = ResourceImageDO::TOKEN_DIMENSION;

        return $allowed;
    }

    /**
     * @param string $token
     * @param string $value
     * @param array $query
     * @return string
     */
    protected function transformTokenToRoute($token, $value, array $query)
    {
        $query = parent::transformTokenToRoute($token, $value, $query);
        switch ($token) {
            case ResourceImageDO::TOKEN_DIMENSION:
                if ((int)$value !== ResourceImageDO::DEFAULT_DIMENSION) {
                    $query['size'] = $value;
                }
                break;
        }

        return $query;
    }
}