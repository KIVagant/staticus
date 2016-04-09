<?php
namespace Staticus\Resources\File;

use Staticus\Resources\ResourceDOAbstract;

/**
 * Domain Object
 * @package Staticus\Resources\File
 */
class ResourceDO extends ResourceDOAbstract
{
    public function getMimeType()
    {
        return 'application/octet-stream';
    }
}