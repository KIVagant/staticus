<?php
namespace Staticus\Resources;

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
    public function setWidth($width = 0);

    /**
     * @return int
     */
    public function getHeight();
    public function setHeight($height = 0);
    public function generateFilePath();
    /**
     * @return int|string
     */
    public function getSize();

    /**
     * @return CropDO|null
     */
    public function getCrop();
}