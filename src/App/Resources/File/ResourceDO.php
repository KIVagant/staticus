<?php
namespace App\Resources\File;

use App\Resources\ResourceDOAbstract;

/**
 * Domain Object
 * @package App\Resources\File
 */
class ResourceDO extends ResourceDOAbstract
{
    public function getMimeType()
    {
        return 'application/octet-stream';
    }
}