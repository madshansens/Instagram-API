<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * MediaData.
 *
 * @method Image_Versions2 getImageVersions2()
 * @method mixed getMediaType()
 * @method mixed getOriginalHeight()
 * @method mixed getOriginalWidth()
 * @method bool isImageVersions2()
 * @method bool isMediaType()
 * @method bool isOriginalHeight()
 * @method bool isOriginalWidth()
 * @method $this setImageVersions2(Image_Versions2 $value)
 * @method $this setMediaType(mixed $value)
 * @method $this setOriginalHeight(mixed $value)
 * @method $this setOriginalWidth(mixed $value)
 * @method $this unsetImageVersions2()
 * @method $this unsetMediaType()
 * @method $this unsetOriginalHeight()
 * @method $this unsetOriginalWidth()
 */
class MediaData extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'image_versions2' => 'Image_Versions2',
        'original_width'  => '',
        'original_height' => '',
        'media_type'      => '',
    ];
}
