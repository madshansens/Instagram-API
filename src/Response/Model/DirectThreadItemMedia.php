<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class DirectThreadItemMedia extends AutoPropertyMapper
{
    const PHOTO = 1;
    const VIDEO = 2;

    const JSON_PROPERTY_MAP = [
        'media_type'      => '',
        'image_versions2' => 'Image_Versions2',
        'video_versions'  => 'VideoVersions[]',
        'original_width'  => '',
        'original_height' => '',
    ];
}
