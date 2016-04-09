<?php
namespace App\Resources\Gif;

use App\Resources\ResourceImageDO;

/**
 * Domain Object
 * @package App\Resources\File
 */
class ResourceDO extends ResourceImageDO
{
    public function getMimeType()
    {
        return 'image/gif';
    }
}