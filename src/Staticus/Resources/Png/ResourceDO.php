<?php
namespace Staticus\Resources\Png;

use Staticus\Resources\ResourceImageDO;

/**
 * Domain Object
 * @package Staticus\Resources\File
 */
class ResourceDO extends ResourceImageDO
{
    const TYPE = 'png';
    protected $type = self::TYPE;
    public function getMimeType()
    {
        return 'image/png';
    }
}