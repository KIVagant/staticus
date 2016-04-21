<?php
namespace Staticus\Resources\Gif;

use Staticus\Resources\Image\ResourceImageDO;

/**
 * Domain Object
 * @package Staticus\Resources\File
 */
class ResourceDO extends ResourceImageDO
{
    const TYPE = 'gif';
    protected $type = self::TYPE;
    public function getMimeType()
    {
        return 'image/gif';
    }
}