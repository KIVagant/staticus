<?php
namespace Staticus\Diactoros\FileContentResponse;

interface FileResponseInterface
{
    /**
     * @return resource
     */
    public function getResource();

    /**
     * @return mixed
     */
    public function getContent();

    /**
     * @param mixed $content
     */
    public function setContent($content);
}
