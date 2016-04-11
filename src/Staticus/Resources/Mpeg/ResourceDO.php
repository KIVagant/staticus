<?php
namespace Staticus\Resources\Mpeg;

use Staticus\Resources\ResourceDOAbstract;

/**
 * Domain Object
 * @package Staticus\Resources\File
 */
class ResourceDO extends ResourceDOAbstract
{
    const TYPE = 'mp3';
    protected $type = self::TYPE;
    public function getMimeType()
    {
        return 'audio/mpeg';
    }
}