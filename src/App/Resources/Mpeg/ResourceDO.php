<?php
namespace App\Resources\Mpeg;

use App\Resources\ResourceDOAbstract;

/**
 * Domain Object
 * @package App\Resources\File
 */
class ResourceDO extends ResourceDOAbstract
{
    public function getMimeType()
    {
        return 'audio/mpeg';
    }
}