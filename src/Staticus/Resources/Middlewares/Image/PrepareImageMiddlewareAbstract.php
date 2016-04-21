<?php
namespace Staticus\Resources\Middlewares\Image;

use Staticus\Exceptions\WrongRequestException;
use Staticus\Resources\Image\CropImageDO;
use Staticus\Resources\Image\ResourceImageDO;
use Staticus\Resources\Image\ResourceImageDOInterface;
use Staticus\Resources\Middlewares\PrepareResourceMiddlewareAbstract;

abstract class PrepareImageMiddlewareAbstract extends PrepareResourceMiddlewareAbstract
{
    protected function fillSpecificResourceSpecific()
    {
        $size = static::getParamFromRequest('size', $this->request);
        $this->parseSizeParameter($size);
        $crop = static::getParamFromRequest('crop', $this->request);
        $this->parseCropParameter($crop);
    }

    protected function parseCropParameter($crop)
    {
        if ($crop) {
            /* @var ResourceImageDOInterface $resource */
            $resource = $this->resourceDO;
            $crop = explode('x', $crop);
            if (count($crop) != 4) {
                throw new WrongRequestException('Crop parameter has to consist of four parts, concatenated by "x" char.',
                    __LINE__);
            }
            $cropObject = new CropImageDO();
            $cropObject->setX((int) $crop[0]);
            $cropObject->setY((int) $crop[1]);
            $cropObject->setWidth((int) $crop[2]);
            $cropObject->setHeight((int) $crop[3]);

            $resizeRatio = $resource->getWidth() / $resource->getHeight();
            $cropRatio = $cropObject->getWidth() / $cropObject->getHeight();

            if ($resizeRatio != $cropRatio) {
                throw new WrongRequestException('Wrong width to height ratio in crop parameter. 
                    It should be same as width to height ratio in size parameter',
                    __LINE__);
            }

            if ($cropObject->getX() < 0 || $cropObject->getY() < 0 ||
                $cropObject->getWidth() < 1 || $cropObject->getHeight() < 1
            ) {
                throw new WrongRequestException('Wrong crop parameter',
                    __LINE__);
            }
            $resource->setCrop($cropObject);
        }
    }

    protected function parseSizeParameter($size)
    {
        $width = ResourceImageDO::DEFAULT_WIDTH;
        $height = ResourceImageDO::DEFAULT_HEIGHT;
        $resource = $this->resourceDO;
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
        /** @var ResourceImageDOInterface $resource */
        $resource->setWidth($width);
        $resource->setHeight($height);
    }
}
