<?php
namespace App\Resources\Png;

use App\Resources\Exceptions\SaveResourceErrorException;
use App\Resources\SaveImageMiddlewareAbstract;

class SaveResourceMiddleware extends SaveImageMiddlewareAbstract
{
    protected static $mimeType = 'image/png';

    protected function writeFile($filePath, $content)
    {
        if (!imagepng($content, $filePath)) {
            imagedestroy($content);
            throw new SaveResourceErrorException('File cannot be written to the path ' . $filePath);
        }
        imagedestroy($content);
    }
}
