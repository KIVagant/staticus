<?php
namespace App\Resources;

use App\Resources\Exceptions\SaveFileErrorException;

class SaveJpgMiddleware extends SaveFileMiddleware
{
    protected static $mimeType = 'image/jpeg';

    protected function writeFile($filePath, $content)
    {
        if (!imagejpeg($content, $filePath)) {
            imagedestroy($content);
            throw new SaveFileErrorException('File cannot be written to the path ' . $filePath);
        }
        imagedestroy($content);
    }
}
