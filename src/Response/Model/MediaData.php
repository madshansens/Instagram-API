<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class MediaData extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'image_versions2' => 'Image_Versions2',
        'original_width'  => '',
        'original_height' => '',
        'media_type'      => '',
    ];
}
