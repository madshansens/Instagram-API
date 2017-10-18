<?php

namespace InstagramAPI\Media;

class Dimensions
{
    /** @var int */
    protected $_width;

    /** @var int */
    protected $_height;

    /** @var float */
    protected $_aspectRatio;

    /**
     * Constructor.
     *
     * @param int $width
     * @param int $height
     */
    public function __construct(
        $width,
        $height)
    {
        $this->_width = (int) $width;
        $this->_height = (int) $height;
        $this->_aspectRatio = $this->_width / $this->_height;
    }

    /**
     * Get stored width for these dimensions.
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * Get stored height for these dimensions.
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * Get stored aspect ratio for these dimensions.
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
        return new self($this->_height, $this->_width);
    }

    /**
     * Create a new, scale-adjusted object.
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

        return new self($newWidth, $newHeight);
    }
}
