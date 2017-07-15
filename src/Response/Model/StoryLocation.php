<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method Attribution getAttribution()
 * @method float getHeight()
 * @method mixed getIsPinned()
 * @method Location getLocation()
 * @method float getRotation()
 * @method float getWidth()
 * @method float getX()
 * @method float getY()
 * @method bool isAttribution()
 * @method bool isHeight()
 * @method bool isIsPinned()
 * @method bool isLocation()
 * @method bool isRotation()
 * @method bool isWidth()
 * @method bool isX()
 * @method bool isY()
 * @method setAttribution(Attribution $value)
 * @method setHeight(float $value)
 * @method setIsPinned(mixed $value)
 * @method setLocation(Location $value)
 * @method setRotation(float $value)
 * @method setWidth(float $value)
 * @method setX(float $value)
 * @method setY(float $value)
 */
class StoryLocation extends AutoPropertyHandler
{
    /**
     * @var float
     */
    public $rotation;
    /**
     * @var float
     */
    public $x;
    /**
     * @var float
     */
    public $y;
    /**
     * @var float
     */
    public $height;
    /**
     * @var float
     */
    public $width;
    /**
     * @var Location
     */
    public $location;
    /**
     * @var Attribution
     */
    public $attribution;
    public $is_pinned;
}
