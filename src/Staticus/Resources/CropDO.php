<?php


namespace Staticus\Resources;


class CropDO
{
    /**
     * Position of cropped area X coordinate of image left top corner
     * @var int
     */
    protected $x;

    /**
     * Position of cropped area Y coordinate of image left top corner
     * @var int
     */
    protected $y;

    /**
     * Width of cropped area
     * @var int
     */
    protected $width;

    /**
     * Height of cropped area
     * @var int
     */
    protected $height;

    /**
     * @return int
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @param int $x
     * @return CropDO
     */
    public function setX($x)
    {
        $this->x = $x;
        return $this;
    }

    /**
     * @return int
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * @param int $y
     * @return CropDO
     */
    public function setY($y)
    {
        $this->y = $y;
        return $this;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $width
     * @return CropDO
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int $height
     * @return CropDO
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    public function __toString()
    {
        return $this->getX() . 'x' . $this->getY() . 'x' . $this->getWidth() . 'x' . $this->getHeight();
    }

    public function toArray()
    {
        $ar = [];
        foreach ($this as $k => $p) {
            $ar[$k] = $p;
        }
        
        return $ar;
    }
}