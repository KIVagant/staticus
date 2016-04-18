<?php
namespace Staticus\Resources\Middlewares;

use Staticus\Exceptions\WrongRequestException;
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
                $width = (int)$size[0];
                $height = (int)$size[1];
                if ($width && $height) {
                    $allowedSizes = $this->config->get('staticus.images.sizes');
                    if (!in_array([$width, $height], $allowedSizes)) {
                        throw new WrongRequestException('Resource size is not allowed: ' . $width . 'x' . $height,
                            __LINE__);
                    }
                }
            }
        }
        /** @var ResourceImageDO $resource */
        $resource->setWidth($width);
        $resource->setHeight($height);
    }
}
