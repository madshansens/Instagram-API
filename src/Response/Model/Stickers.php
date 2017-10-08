<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * @method string getId()
 * @method mixed getImageHeight()
 * @method mixed getImageUrl()
 * @method mixed getImageWidth()
 * @method mixed getImageWidthRatio()
 * @method mixed getName()
 * @method mixed getTrayImageWidthRatio()
 * @method mixed getType()
 * @method bool isId()
 * @method bool isImageHeight()
 * @method bool isImageUrl()
 * @method bool isImageWidth()
 * @method bool isImageWidthRatio()
 * @method bool isName()
 * @method bool isTrayImageWidthRatio()
 * @method bool isType()
 * @method $this setId(string $value)
 * @method $this setImageHeight(mixed $value)
 * @method $this setImageUrl(mixed $value)
 * @method $this setImageWidth(mixed $value)
 * @method $this setImageWidthRatio(mixed $value)
 * @method $this setName(mixed $value)
 * @method $this setTrayImageWidthRatio(mixed $value)
 * @method $this setType(mixed $value)
 * @method $this unsetId()
 * @method $this unsetImageHeight()
 * @method $this unsetImageUrl()
 * @method $this unsetImageWidth()
 * @method $this unsetImageWidthRatio()
 * @method $this unsetName()
 * @method $this unsetTrayImageWidthRatio()
 * @method $this unsetType()
 */
class Stickers extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'id'                     => 'string',
        'tray_image_width_ratio' => '',
        'image_height'           => '',
        'image_width_ratio'      => '',
        'type'                   => '',
        'image_width'            => '',
        'name'                   => '',
        'image_url'              => '',
    ];
}
