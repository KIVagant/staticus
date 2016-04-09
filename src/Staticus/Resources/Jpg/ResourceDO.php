<?php
namespace Staticus\Resources\Jpg;

use Staticus\Resources\ResourceImageDO;

/**
 * Domain Object
 * @package Staticus\Resources\File
 */
class ResourceDO extends ResourceImageDO
{
    public function getMimeType()
    {
        return 'image/jpeg';
    }
}