<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class DirectThreadItemMedia extends AutoPropertyHandler
{
    const PHOTO = 1;
    const VIDEO = 2;

    public $media_type;
    /**
     * @var Image_Versions2
     */
    public $image_versions2;
    /**
     * @var VideoVersions[]
     */
    public $video_versions;
    public $original_width;
    public $original_height;
}
