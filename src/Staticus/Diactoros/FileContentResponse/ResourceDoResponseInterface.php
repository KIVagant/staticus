<?php
namespace Staticus\Diactoros\FileContentResponse;

use Staticus\Resources\ResourceDOInterface;

interface ResourceDoResponseInterface
{
    /**
     * @return ResourceDOInterface
     */
    public function getContent();

    /**
     * @param ResourceDOInterface $content
     */
    public function setContent(ResourceDOInterface $content);
}