<?php
namespace App\Resources;

interface ResourceImageDOInterface extends ResourceDOInterface
{
    /**
     * @return int
     */
    public function getWidth();

    /**
     * @param mixed $width
     * @return ResourceImageDO
     */
    public function setWidth($width);

    /**
     * @return int
     */
    public function getHeight();
    public function setHeight($height);
    public function generateFilePath();
    /**
     * @return int|string
     */
    public function getSize();
}