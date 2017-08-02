<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method Image_Versions2 getImageVersions2()
 * @method mixed getMediaType()
 * @method mixed getOriginalHeight()
 * @method mixed getOriginalWidth()
 * @method bool isImageVersions2()
 * @method bool isMediaType()
 * @method bool isOriginalHeight()
 * @method bool isOriginalWidth()
 * @method setImageVersions2(Image_Versions2 $value)
 * @method setMediaType(mixed $value)
 * @method setOriginalHeight(mixed $value)
 * @method setOriginalWidth(mixed $value)
 */
class MediaData extends AutoPropertyHandler
{
    /**
     * @var Image_Versions2
     */
    public $image_versions2;
    public $original_width;
    public $original_height;
    public $media_type;
}
