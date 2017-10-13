<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * ImageCandidate.
 *
 * @method mixed getHeight()
 * @method mixed getUrl()
 * @method mixed getWidth()
 * @method bool isHeight()
 * @method bool isUrl()
 * @method bool isWidth()
 * @method $this setHeight(mixed $value)
 * @method $this setUrl(mixed $value)
 * @method $this setWidth(mixed $value)
 * @method $this unsetHeight()
 * @method $this unsetUrl()
 * @method $this unsetWidth()
 */
class ImageCandidate extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'url'    => '',
        'width'  => '',
        'height' => '',
    ];
}
