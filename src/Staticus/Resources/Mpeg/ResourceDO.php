<?php
namespace Staticus\Resources\Mpeg;

use Staticus\Resources\ResourceDOAbstract;

/**
 * Domain Object
 * @package Staticus\Resources\File
 */
class ResourceDO extends ResourceDOAbstract
{
    public function getMimeType()
    {
        return 'audio/mpeg';
    }
}