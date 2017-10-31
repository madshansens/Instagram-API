<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * StoryHashtag.
 *
 * @method Hashtag getHashtag()
 * @method float getHeight()
 * @method int getIsPinned()
 * @method float getRotation()
 * @method float getWidth()
 * @method float getX()
 * @method float getY()
 * @method bool isHashtag()
 * @method bool isHeight()
 * @method bool isIsPinned()
 * @method bool isRotation()
 * @method bool isWidth()
 * @method bool isX()
 * @method bool isY()
 * @method $this setHashtag(Hashtag $value)
 * @method $this setHeight(float $value)
 * @method $this setIsPinned(int $value)
 * @method $this setRotation(float $value)
 * @method $this setWidth(float $value)
 * @method $this setX(float $value)
 * @method $this setY(float $value)
 * @method $this unsetHashtag()
 * @method $this unsetHeight()
 * @method $this unsetIsPinned()
 * @method $this unsetRotation()
 * @method $this unsetWidth()
 * @method $this unsetX()
 * @method $this unsetY()
 */
class StoryHashtag extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'x'         => 'float',
        'y'         => 'float',
        'width'     => 'float',
        'height'    => 'float',
        'rotation'  => 'float',
        'is_pinned' => 'int',
        'hashtag'   => 'Hashtag',
    ];
}
