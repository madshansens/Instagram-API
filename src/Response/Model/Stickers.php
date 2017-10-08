<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

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
