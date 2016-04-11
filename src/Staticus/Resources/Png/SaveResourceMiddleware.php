<?php
namespace Staticus\Resources\Png;

use Staticus\Resources\Exceptions\SaveResourceErrorException;
use Staticus\Resources\Middlewares\SaveImageMiddlewareAbstract;

class SaveResourceMiddleware extends SaveImageMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO)
    {
        parent::__construct($resourceDO);
    }
    protected function writeFile($filePath, $content)
    {
        if (!imagepng($content, $filePath)) {
            imagedestroy($content);
            throw new SaveResourceErrorException('File cannot be written to the path ' . $filePath, __LINE__);
        }
        imagedestroy($content);
    }
}
