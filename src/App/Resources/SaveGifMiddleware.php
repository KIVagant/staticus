<?php
namespace App\Resources;

use App\Resources\Exceptions\SaveFileErrorException;

class SaveGifMiddleware extends SaveFileMiddleware
{
    protected static $mimeType = 'image/gif';

    protected function writeFile($filePath, $content)
    {
        if (!imagegif($content, $filePath)) {
            imagedestroy($content);
            throw new SaveFileErrorException('File cannot be written to the path ' . $filePath);
        }
        imagedestroy($content);
    }
}
