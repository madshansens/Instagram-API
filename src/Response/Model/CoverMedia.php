<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * CoverMedia.
 *
 * @method string getId()
 * @method Image_Versions2 getImageVersions2()
 * @method int getMediaType()
 * @method mixed getOriginalHeight()
 * @method mixed getOriginalWidth()
 * @method bool isId()
 * @method bool isImageVersions2()
 * @method bool isMediaType()
 * @method bool isOriginalHeight()
 * @method bool isOriginalWidth()
 * @method $this setId(string $value)
 * @method $this setImageVersions2(Image_Versions2 $value)
 * @method $this setMediaType(int $value)
 * @method $this setOriginalHeight(mixed $value)
 * @method $this setOriginalWidth(mixed $value)
 * @method $this unsetId()
 * @method $this unsetImageVersions2()
 * @method $this unsetMediaType()
 * @method $this unsetOriginalHeight()
 * @method $this unsetOriginalWidth()
 */
class CoverMedia extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'id'              => 'string',
        /*
         * A number describing what type of media this is.
         */
        'media_type'      => 'int',
        'image_versions2' => 'Image_Versions2',
        'original_width'  => '',
        'original_height' => '',
    ];
}
