<?php
namespace Staticus\Resources\Middlewares;

use Staticus\Resources\ResourceImageDO;

abstract class PrepareImageMiddlewareAbstract extends PrepareResourceMiddlewareAbstract
{
    protected function fillSpecificResourceSpecific()
    {
        $width = ResourceImageDO::DEFAULT_WIDTH;
        $height = ResourceImageDO::DEFAULT_HEIGHT;
        $resource = $this->resourceDO;
        $size = static::getParamFromRequest('size', $this->request);
        if ($size) {
            $size = explode('x', $size);
            if (!empty($size[0]) && !empty($size[1])) {
                $width = $size[0];
                $height = $size[1];
            }
        }
        /** @var ResourceImageDO $resource */
        $resource->setWidth($width);
        $resource->setHeight($height);
    }
}
