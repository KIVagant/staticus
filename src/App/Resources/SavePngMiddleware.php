<?php
namespace App\Resources;

use App\Resources\Exceptions\SaveFileErrorException;

class SavePngMiddleware extends SaveFileMiddleware
{
    protected static $mimeType = 'image/png';

    protected function writeFile($filePath, $content)
    {
        if (!imagepng($content, $filePath)) {
            imagedestroy($content);
            throw new SaveFileErrorException('File cannot be written to the path ' . $filePath);
        }
        imagedestroy($content);
    }
}
