<?php
namespace App\Resources\Jpg;

use App\Resources\Exceptions\SaveResourceErrorException;
use App\Resources\SaveImageMiddlewareAbstract;

class SaveResourceMiddleware extends SaveImageMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO)
    {
        parent::__construct($resourceDO);
    }
    protected function writeFile($filePath, $content)
    {
        if (!imagejpeg($content, $filePath)) {
            imagedestroy($content);
            throw new SaveResourceErrorException('File cannot be written to the path ' . $filePath);
        }
        imagedestroy($content);
    }
}
