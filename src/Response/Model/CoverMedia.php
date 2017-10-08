<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class CoverMedia extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'id'              => 'string',
        'media_type'      => '',
        'image_versions2' => 'Image_Versions2',
        'original_width'  => '',
        'original_height' => '',
    ];
}
