<?php

namespace InstagramAPI\Media;

class Rectangle
{
    /** @var int */
    protected $_x;

    /** @var int */
    protected $_y;

    /** @var int */
    protected $_width;

    /** @var int */
    protected $_height;

    /**
     * Constructor.
     *
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     */
    public function __construct(
        $x,
        $y,
        $width,
        $height)
    {
        $this->_x = $x;
        $this->_y = $y;
        $this->_width = $width;
        $this->_height = $height;
    }

    /**
     * @return int
     */
    public function getX()
    {
        return $this->_x;
    }

    /**
     * @return int
     */
    public function getY()
    {
        return $this->_y;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * Create a new object with swapped axes.
     *
     * @return self
     */
    public function createSwappedAxes()
    {
        return new self($this->_y, $this->_x, $this->_height, $this->_width);
    }
}
