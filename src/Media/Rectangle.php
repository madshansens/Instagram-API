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

    /** @var float */
    protected $_aspectRatio;

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
        $this->_x = (int) $x;
        $this->_y = (int) $y;
        $this->_width = (int) $width;
        $this->_height = (int) $height;
        $this->_aspectRatio = $this->_width / $this->_height;
    }

    /**
     * Get stored X1 offset for this rectangle.
     *
     * @return int
     */
    public function getX()
    {
        return $this->_x;
    }

    /**
     * Get stored Y1 offset for this rectangle.
     *
     * @return int
     */
    public function getY()
    {
        return $this->_y;
    }

    /**
     * Get stored X1 offset for this rectangle.
     *
     * This does the same thing as `getX()`. It is just a mental
     * convenience when working in X1/X2 space.
     *
     * @return int
     */
    public function getX1()
    {
        return $this->_x;
    }

    /**
     * Get stored Y1 offset for this rectangle.
     *
     * This does the same thing as `getY()`. It is just a mental
     * convenience when working in Y1/Y2 space.
     *
     * @return int
     */
    public function getY1()
    {
        return $this->_y;
    }

    /**
     * Get calculated X2 offset (X1+Width) for this rectangle.
     *
     * @return int
     */
    public function getX2()
    {
        return $this->_x + $this->_width;
    }

    /**
     * Get calculated Y2 offset (Y1+Height) for this rectangle.
     *
     * @return int
     */
    public function getY2()
    {
        return $this->_y + $this->_height;
    }

    /**
     * Get stored width for this rectangle.
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * Get stored height for this rectangle.
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * Get stored aspect ratio for this rectangle.
     *
     * @return int
     */
    public function getAspectRatio()
    {
        return $this->_aspectRatio;
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

    /**
     * Create a new, scale-adjusted object.
     *
     * NOTE: The x/y offsets are not affected. Only the width and height.
     *
     * You can trust that this algorithm will always use `ceil()` to choose the
     * maximum number of pixels necessary to fit everything at the new scale.
     *
     * @param float $newScale The scale factor to apply.
     *
     * @return self
     */
    public function createScaled(
        $newScale = 1.0)
    {
        // NOTE: We MUST use ceil() to guarantee that all intended pixels fit.
        $newWidth = (int) ceil($newScale * $this->_width);
        $newHeight = (int) ceil($newScale * $this->_height);

        return new self($this->_x, $this->_y, $newWidth, $newHeight);
    }
}
