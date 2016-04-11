<?php
namespace Staticus\Resources\Middlewares;

use Staticus\Resources\ResourceDOInterface;
use Staticus\Resources\ResourceImageDO;

abstract class ImageResponseMiddlewareAbstract extends ResourceResponseMiddlewareAbstract
{
    protected function getUri(ResourceDOInterface $resourceDO)
    {
        /** @var ResourceImageDO $resourceDO */
        $uri = $resourceDO->getName() . '.' . $resourceDO->getType();
        $query = [];
        if (ResourceDOInterface::DEFAULT_VARIANT !== $resourceDO->getVariant()) {
            $query['var'] = $resourceDO->getVariant();
        }
        if (ResourceDOInterface::DEFAULT_VERSION !== $resourceDO->getVersion()) {
            $query['v'] = $resourceDO->getVersion();
        }
        if (ResourceImageDO::DEFAULT_SIZE !== $resourceDO->getSize()) {
            $query['size'] = $resourceDO->getSize();
        }
        $query = http_build_query($query);
        if ($query) {
            $uri .= '?' . $query;
        }

        return $uri;
    }
}
