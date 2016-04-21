<?php
namespace Staticus\Resources\Image;


interface CropImageDOInterface
{
    /**
     * @return int
     */
    public function getX();

    /**
     * @param int $x
     * @return CropImageDOInterface
     */
    public function setX($x);

    /**
     * @return int
     */
    public function getY();

    /**
     * @param int $y
     * @return CropImageDOInterface
     */
    public function setY($y);

    /**
     * @return int
     */
    public function getWidth();

    /**
     * @param int $width
     * @return CropImageDOInterface
     */
    public function setWidth($width);

    /**
     * @return int
     */
    public function getHeight();

    /**
     * @param int $height
     * @return CropImageDOInterface
     */
    public function setHeight($height);

    public function toArray();
}