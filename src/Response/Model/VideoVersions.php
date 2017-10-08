<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * @method mixed getHeight()
 * @method string getId()
 * @method mixed getType()
 * @method mixed getUrl()
 * @method mixed getWidth()
 * @method bool isHeight()
 * @method bool isId()
 * @method bool isType()
 * @method bool isUrl()
 * @method bool isWidth()
 * @method $this setHeight(mixed $value)
 * @method $this setId(string $value)
 * @method $this setType(mixed $value)
 * @method $this setUrl(mixed $value)
 * @method $this setWidth(mixed $value)
 * @method $this unsetHeight()
 * @method $this unsetId()
 * @method $this unsetType()
 * @method $this unsetUrl()
 * @method $this unsetWidth()
 */
class VideoVersions extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'url'    => '',
        'type'   => '',
        'width'  => '',
        'height' => '',
        'id'     => 'string',
    ];
}
