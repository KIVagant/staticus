<?php
namespace App\Resources\Gif;

use App\Resources\Exceptions\SaveResourceErrorException;
use App\Resources\SaveImageMiddlewareAbstract;

class SaveResourceMiddleware extends SaveImageMiddlewareAbstract
{
    protected static $mimeType = 'image/gif';

    protected function writeFile($filePath, $content)
    {
        if (!imagegif($content, $filePath)) {
            imagedestroy($content);
            throw new SaveResourceErrorException('File cannot be written to the path ' . $filePath);
        }
        imagedestroy($content);
    }
}
